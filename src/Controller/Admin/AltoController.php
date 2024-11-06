<?php

namespace Alto\Controller\Admin;

use Alto\Form\ImportForm;
use Alto\Job\BatchImport;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\File\TempFileFactory;
use Omeka\Stdlib\Message;

class AltoController extends AbstractActionController
{
    protected TempFileFactory $tempFileFactory;

    public function __construct(TempFileFactory $tempFileFactory)
    {
        $this->tempFileFactory = $tempFileFactory;
    }

    public function importAction()
    {
        $form = $this->getForm(ImportForm::class);

        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $form->setData($data);
            if ($form->isValid()) {
                $fileData = $this->params()->fromFiles();

                $jobArgs = [
                    'replace' => $data['replace'] ? true : false,
                    'files' => [],
                ];

                foreach ($fileData['files'] as $file) {
                    $tempFile = $this->tempFileFactory->build();
                    if (!copy($file['tmp_name'], $tempFile->getTempPath())) {
                        throw new \Exception(sprintf('Failed to copy %s to %s', $file['tmp_name'], $tempFile->getTempPath()));
                    }

                    $jobArgs['files'][] = [
                        'name' => $file['name'],
                        'path' => $tempFile->getTempPath(),
                    ];
                }

                $hyperlinkViewHelper = $this->viewHelpers()->get('hyperlink');
                $job = $this->jobDispatcher()->dispatch(BatchImport::class, $jobArgs);
                $message = new Message(
                    'Import started in job %s', // @translate
                    $hyperlinkViewHelper('#' . $job->getId(), $this->url()->fromRoute('admin/id', ['controller' => 'job', 'id' => $job->getId()]))
                );
                $message->setEscapeHtml(false);
                $this->messenger()->addSuccess($message);

                return $this->redirect()->toRoute(null, [], [], true);
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;

        $view->setVariable('form', $form);

        return $view;
    }
}
