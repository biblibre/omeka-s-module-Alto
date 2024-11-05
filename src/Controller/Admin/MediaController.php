<?php

namespace Alto\Controller\Admin;

use Alto\Form\MediaForm;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Form\ConfirmForm;
use Omeka\Mvc\Exception\NotFoundException;

class MediaController extends AbstractActionController
{
    public function sidebarAttachAction()
    {
        $mediaId = $this->params()->fromRoute('media-id');

        $media = $this->api()->read('media', $mediaId)->getContent();
        $altoDocument = $this->api()->searchOne('alto_documents', ['media_id' => $media->id()])->getContent();

        $form = $this->getForm(MediaForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('admin/alto/media-id', ['action' => 'attach', 'media-id' => $mediaId]));

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('altoDocument', $altoDocument);
        $view->setVariable('media', $media);
        $view->setVariable('form', $form);

        return $view;
    }

    public function attachAction()
    {
        $mediaId = $this->params()->fromRoute('media-id');

        $media = $this->api()->read('media', $mediaId)->getContent();
        $altoDocument = $this->api()->searchOne('alto_documents', ['media_id' => $mediaId])->getContent();

        if ($this->getRequest()->isPost()) {
            $form = $this->getForm(MediaForm::class);
            $form->setData($this->params()->fromPost());
            if ($form->isValid()) {
                $fileData = $this->getRequest()->getFiles()->toArray();
                $xml = file_get_contents($fileData['file']['tmp_name']);
                if ($altoDocument) {
                    $response = $this->api($form)->update('alto_documents', $altoDocument->id(), ['o:xml' => $xml], [], ['isPartial' => true]);
                } else {
                    $response = $this->api($form)->create('alto_documents', ['o:media_id' => $media->id(), 'o:xml' => $xml]);
                }
                $this->messenger()->addSuccess('ALTO document attached'); // @translate
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        return $this->redirect()->toRoute(
            'admin/id',
            ['controller' => 'media', 'action' => 'show', 'id' => $media->id()],
            ['fragment' => 'alto']
        );
    }

    public function deleteConfirmAction()
    {
        $altoDocument = $this->api()->searchOne('alto_documents', ['media_id' => $this->params('media-id')])->getContent();

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('common/delete-confirm-details');
        $view->setVariable('resource', $altoDocument);
        $view->setVariable('resourceLabel', 'ALTO document'); // @translate

        return $view;
    }

    public function deleteAction()
    {
        $mediaId = $this->params()->fromRoute('media-id');

        if ($this->getRequest()->isPost()) {
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $altoDocument = $this->api()->searchOne('alto_documents', ['media_id' => $mediaId])->getContent();
                if ($altoDocument) {
                    $response = $this->api($form)->delete('alto_documents', $altoDocument->id());
                    if ($response) {
                        $this->messenger()->addSuccess('ALTO document deleted'); // @translate
                    }
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        return $this->redirect()->toRoute(
            'admin/id',
            ['controller' => 'media', 'action' => 'show', 'id' => $mediaId],
            ['fragment' => 'alto'],
        );
    }
}
