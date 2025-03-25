<?php

namespace Alto\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class AltoDocumentAdapter extends AbstractEntityAdapter
{
    protected $sortFields = [
        'id' => 'id',
        'created' => 'created',
        'modified' => 'modified',
    ];

    public function getResourceName()
    {
        return 'alto_documents';
    }

    public function getRepresentationClass()
    {
        return 'Alto\Api\Representation\AltoDocumentRepresentation';
    }

    public function getEntityClass()
    {
        return 'Alto\Entity\AltoDocument';
    }

    public function hydrate(Request $request, EntityInterface $entity, ErrorStore $errorStore)
    {
        if ($this->shouldHydrate($request, 'o:media_id')) {
            $mediaAdapter = $this->getAdapter('media');
            $entity->setMedia($mediaAdapter->findEntity($request->getValue('o:media_id')));
        }

        if ($this->shouldHydrate($request, 'o:xml')) {
            $settings = $this->getServiceLocator()->get('Omeka\Settings');
            $xml = $request->getValue('o:xml');
            if ($settings->get('alto_compression', false)) {
                $entity->setXml(null);
                $entity->setXmlCompressed(isset($xml) ? gzencode($xml) : null);
            } else {
                $entity->setXml($xml);
                $entity->setXmlCompressed(null);
            }
        }
    }

    public function validateEntity(EntityInterface $entity, ErrorStore $errorStore)
    {
        if (null === $entity->getMedia()) {
            $errorStore->addError('o:media_id', 'An ALTO document needs to be associated with a media');
        }

        if (!$this->isUnique($entity, ['media' => $entity->getMedia()])) {
            $errorStore->addError('o:media_id', 'This media is already associated with an ALTO document');
        }

        $xml = $entity->getXml() ?? $entity->getXmlCompressed();
        if ($xml === null || trim($xml) === '') {
            $errorStore->addError('o:xml', 'XML cannot be empty');
        }
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {
        if (isset($query['media_id'])) {
            $qb->andWhere($qb->expr()->eq(
                'omeka_root.media',
                $this->createNamedParameter($qb, $query['media_id']))
            );
        }
    }
}
