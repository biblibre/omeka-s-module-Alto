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
            // If the filename has multiple extensions (eg. "file.alto.xml"),
            // try all prefixes, from longest to shortest, until we find a
            // match (eg. "file.alto", then "file")
            $basename = pathinfo($file['name'], PATHINFO_BASENAME);
            $prefixes = [];
            while ('' !== ($prefix = pathinfo($basename, PATHINFO_FILENAME)) && $prefix !== $basename) {
                $prefixes[] = $prefix;
                $basename = $prefix;
            }

            $medias = [];
            foreach ($prefixes as $prefix) {
                $query = $em->createQuery('SELECT m.id, m.source FROM Omeka\Entity\Media m WHERE REGEXP(m.source, :regexp) = 1');
                $regexp = '(^|/)' . preg_quote($prefix) . "\\.[a-zA-Z0-9]+$";
                $query->setParameter('regexp', $regexp);

                $medias = $query->getScalarResult();
                if (!empty($medias)) {
                    break;
                }
            }

            if (empty($medias)) {
                $logger->err(sprintf('No media found for file %s', $file['name']));
                continue;
            } elseif (count($medias) > 1) {
                $logger->err(sprintf('More than one media found for file %s', $file['name']));
                continue;
            }

            $media = reset($medias);
            $mediaId = $media['id'];
            $logger->info(sprintf('File %s matched media %d (%s)', $file['name'], $mediaId, $media['source']));

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
