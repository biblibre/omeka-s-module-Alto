<?php

namespace Alto\Job;

use Alto\Reader\AltoReader;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Omeka\Job\AbstractJob;

class BatchImport extends AbstractJob
{
    public function perform()
    {
        $serviceLocator = $this->getServiceLocator();
        $api = $serviceLocator->get('Omeka\ApiManager');
        $em = $serviceLocator->get('Omeka\EntityManager');
        $logger = $serviceLocator->get('Omeka\Logger');

        $files = $this->getArg('files', []);
        if (empty($files)) {
            return;
        }

        $replace = $this->getArg('replace', false);

        foreach ($files as $file) {
            $query = $em->createQuery('SELECT m.id FROM Omeka\Entity\Media m WHERE REGEXP(m.source, :regexp) = 1');
            $basename = pathinfo($file['name'], PATHINFO_FILENAME);
            $regexp = '(^|/)' . preg_quote($basename) . "\\.[a-zA-Z0-9]+$";
            $query->setParameter('regexp', $regexp);

            try {
                $mediaId = $query->getSingleScalarResult();
            } catch (NoResultException $e) {
                $logger->err(sprintf('No media found for file %s', $file['name']));
                continue;
            } catch (NonUniqueResultException $e) {
                $logger->err(sprintf('More than one media found for file %s', $file['name']));
                continue;
            }

            $logger->info(sprintf('File %s matched media %d', $file['name'], $mediaId));

            $altoDocuments = $api->search('alto_documents', ['media_id' => $mediaId])->getContent();
            if (empty($altoDocuments)) {
                try {
                    $xml = file_get_contents($file['path']);
                    $api->create('alto_documents', ['o:media_id' => $mediaId, 'o:xml' => $xml]);
                    $logger->info(sprintf('Attached %s to media %d', $file['name'], $mediaId));
                } catch (\Exception $e) {
                    $logger->err(sprintf('Failed to attach %s to media %d: %s', $file['name'], $mediaId, $e));
                }
            } elseif ($replace) {
                $altoDocument = reset($altoDocuments);

                try {
                    $xml = file_get_contents($file['path']);
                    $api->update('alto_documents', $altoDocument->id(), ['o:xml' => $xml], [], ['isPartial' => true]);
                    $logger->info(sprintf('Attached %s to media %d (replacement)', $file['name'], $mediaId));
                } catch (\Exception $e) {
                    $logger->err(sprintf('Failed to attach %s to media %d: %s', $file['name'], $mediaId, $e));
                }
            } else {
                $logger->info(sprintf('Media %d already has an ALTO document attached', $mediaId));
            }
        }
    }
}
