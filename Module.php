<?php

namespace Alto;

use Alto\Reader\AltoReader;
use Composer\Semver\Comparator;
use Omeka\Module\AbstractModule;
use Omeka\Permissions\Acl;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;

class Module extends AbstractModule
{
    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(null, 'Alto\Api\Adapter\AltoDocumentAdapter');
        $acl->allow(null, 'Alto\Entity\AltoDocument', 'read');
        $acl->allow(
            [Acl::ROLE_EDITOR, Acl::ROLE_REVIEWER, Acl::ROLE_AUTHOR],
            'Alto\Entity\AltoDocument',
            ['create', 'update', 'delete']
        );

        $acl->allow(
            [Acl::ROLE_EDITOR, Acl::ROLE_REVIEWER, Acl::ROLE_AUTHOR],
            'Alto\Controller\Admin\Media'
        );

        // Make alto document visible only if the corresponding media is visible
        // This also works when the module Group is enabled
        $em = $this->getServiceLocator()->get('Omeka\EntityManager');
        $filters = $em->getFilters();
        if ($filters->isEnabled('resource_visibility')) {
            $filter = $filters->getFilter('resource_visibility');
            $filter->addRelatedEntity('Alto\Entity\AltoDocument', 'media_id');
        }
    }

    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');

        $connection->executeStatement(<<<SQL
            CREATE TABLE alto_document (
                id INT AUTO_INCREMENT NOT NULL,
                media_id INT NOT NULL,
                xml LONGTEXT DEFAULT NULL,
                xml_compressed LONGBLOB DEFAULT NULL,
                created DATETIME NOT NULL,
                modified DATETIME DEFAULT NULL,
                UNIQUE INDEX UNIQ_8FFEC1E4EA9FDD75 (media_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $connection->executeStatement(<<<SQL
            ALTER TABLE alto_document
            ADD CONSTRAINT FK_8FFEC1E4EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE
        SQL);
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');
        $connection->executeStatement('DROP TABLE alto_document');
    }

    public function upgrade($oldVersion, $newVersion, ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');

        if (Comparator::lessThan($oldVersion, '0.2.0')) {
            $connection->executeStatement('ALTER TABLE alto_document ADD COLUMN xml_compressed LONGBLOB NULL AFTER xml');
            $connection->executeStatement('ALTER TABLE alto_document MODIFY COLUMN xml LONGTEXT NULL');
        }
    }

    public function getConfigForm(PhpRenderer $renderer)
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $settings = $services->get('Omeka\Settings');

        $form = $formElementManager->get(Form\ConfigForm::class);
        $form->setData([
            'alto_compression' => $settings->get('alto_compression'),
        ]);
        $form->prepare();

        return $renderer->formCollection($form);
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $settings = $services->get('Omeka\Settings');
        $form = $formElementManager->get(Form\ConfigForm::class);
        $form->setData($controller->params()->fromPost());
        if (!$form->isValid()) {
            $controller->messenger()->addFormErrors($form);
            return false;
        }

        $data = $form->getData();

        $start_compression_job = $data['alto_start_compression_job'];
        unset($data['alto_start_compression_job']);

        foreach ($data as $key => $value) {
            $settings->set($key, $value);
        }

        if ($start_compression_job) {
            $job = $controller->jobDispatcher()->dispatch(Job\UpdateCompression::class);
            $message = new \Omeka\Stdlib\Message(
                'Compression job started. %s', // @translate
                sprintf(
                    '<a href="%s">%s</a>',
                    $controller->url()->fromRoute('admin/id', ['controller' => 'job', 'id' => $job->getId()]),
                    $controller->translate('See job details'),
                )
            );
            $message->setEscapeHtml(false);
            $controller->messenger()->addSuccess($message);
        }

        return true;
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Media',
            'view.show.section_nav',
            [$this, 'onViewShowSectionNavForAdminMedia']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Media',
            'view.show.after',
            [$this, 'onViewShowAfterForAdminMedia']
        );

        $sharedEventManager->attach(
            'Omeka\Api\Representation\MediaRepresentation',
            'rep.resource.json',
            [$this, 'onRepResourceJsonForMedia']
        );

        $sharedEventManager->attach(
            'Solr\ValueExtractor\ItemValueExtractor',
            'solr.value_extractor.fields',
            [$this, 'onSolrValueExtractorFieldsForItemValueExtractor']
        );
        $sharedEventManager->attach(
            'Solr\ValueExtractor\ItemValueExtractor',
            'solr.value_extractor.extract_value',
            [$this, 'onSolrValueExtractorExtractValueForItemValueExtractor']
        );
    }

    public function onViewShowSectionNavForAdminMedia(Event $event)
    {
        $section_nav = $event->getParam('section_nav');
        $section_nav['alto'] = 'ALTO';
        $event->setParam('section_nav', $section_nav);
    }

    public function onViewShowAfterForAdminMedia(Event $event)
    {
        $serviceLocator = $this->getServiceLocator();
        $api = $serviceLocator->get('Omeka\ApiManager');

        $view = $event->getTarget();
        $media = $view->get('media');

        $altoDocuments = $api->search('alto_documents', ['media_id' => $media->id()])->getContent();
        $altoDocument = reset($altoDocuments) ?: null;

        echo $view->partial('alto/admin/media/section-alto', ['media' => $media, 'altoDocument' => $altoDocument]);
    }

    public function onRepResourceJsonForMedia(Event $event)
    {
        $media = $event->getTarget();
        $jsonLd = $event->getParam('jsonLd');
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $altoDocuments = $api->search('alto_documents', ['media_id' => $media->id()])->getContent();
        if (!empty($altoDocuments)) {
            $altoDocument = reset($altoDocuments);
            $jsonLd['o-module-alto:alto'] = $altoDocument->getReference();
            $event->setParam('jsonLd', $jsonLd);
        }
    }

    public function onSolrValueExtractorFieldsForItemValueExtractor(Event $event)
    {
        $fields = $event->getParam('fields');

        $fields['media']['children']['alto_text_content'] = [
            'label' => 'ALTO text content', // @translate
        ];

        $event->setParam('fields', $fields);
    }

    public function onSolrValueExtractorExtractValueForItemValueExtractor(Event $event)
    {
        $item = $event->getTarget();
        $field = $event->getParam('field');

        if ($field === 'media/alto_text_content') {
            $services = $this->getServiceLocator();
            $em = $services->get('Omeka\EntityManager');
            $apiAdapters = $services->get('Omeka\ApiAdapterManager');
            $altoDocumentAdapter = $apiAdapters->get('alto_documents');

            $query = $em->createQuery('SELECT d FROM Alto\Entity\AltoDocument d JOIN d.media m WHERE m.item = :item_id');
            $query->setParameter('item_id', $item->id());
            $altoDocuments = $query->getResult();

            $texts = [];
            foreach ($altoDocuments as $altoDocument) {
                $altoDocumentRepresentation = $altoDocumentAdapter->getRepresentation($altoDocument);
                $xml = $altoDocumentRepresentation->xml();
                if (isset($xml)) {
                    $text = AltoReader::extractTextFromXml($xml);
                    if ($text !== '') {
                        $texts[] = $text;
                    }
                }
                $em->detach($altoDocument);
            }

            if (!empty($texts)) {
                $event->setParam('value', implode("\n", $texts));
            }
        }
    }

    public function getConfig()
    {
        return require __DIR__ . '/config/module.config.php';
    }
}
