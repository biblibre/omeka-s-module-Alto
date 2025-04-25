<?php
namespace Alto\Job;

use Omeka\Job\AbstractJob;

class UpdateCompression extends AbstractJob
{
    public function perform()
    {
        $services = $this->getServiceLocator();
        $connection = $services->get('Omeka\Connection');
        $logger = $services->get('Omeka\Logger');
        $settings = $services->get('Omeka\Settings');
        $em = $services->get('Omeka\EntityManager');

        $logger->info('Job started');

        $alto_compression = $settings->get('alto_compression', false);
        $logger->info(sprintf('Compression is %s', $alto_compression ? 'enabled' : 'disabled'));

        $em->flush();

        if ($alto_compression) {
            $stmt = $connection->prepare('select id, xml from alto_document where xml is not null and id > :lastId limit 1');
        } else {
            $stmt = $connection->prepare('select id, xml_compressed from alto_document where xml_compressed is not null and id > :lastId limit 1');
        }

        $lastId = 0;
        $updated = 0;
        while (1) {
            if ($this->shouldStop()) {
                $logger->info('Job stopped');
                return;
            }

            $stmt->bindValue('lastId', $lastId);
            $result = $stmt->executeQuery();
            $row = $result->fetchAssociative();
            if (!$row) {
                break;
            }

            $lastId = $row['id'];

            if ($alto_compression) {
                $data = [
                    'xml' => null,
                    'xml_compressed' => gzencode($row['xml']),
                ];
            } else {
                $data = [
                    'xml' => gzdecode($row['xml_compressed']),
                    'xml_compressed' => null,
                ];
            }

            $connection->update('alto_document', $data, ['id' => $row['id']]);

            $updated++;

            if ($updated % 100 === 0) {
                $logger->info(sprintf('Number of updated ALTO documents so far: %d', $updated));
                $em->flush();
            }
        }

        $logger->info(sprintf('Total number of updated ALTO documents: %d', $updated));

        $logger->info('Job ended normally');
    }
}
