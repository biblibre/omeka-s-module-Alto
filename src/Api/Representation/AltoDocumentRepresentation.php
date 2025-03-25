<?php

namespace Alto\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;
use Alto\Api\Adapter\AltoDocumentAdapter;
use Alto\Entity\AltoDocument;
use Alto\Reader\AltoReader;

class AltoDocumentRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLdType()
    {
        return 'o-module-alto:Document';
    }

    public function getJsonLd()
    {
        $entity = $this->resource;

        return [
            'o:media' => $this->media()->getReference(),
            'o:xml' => $this->xml(),
            'o:created' => $this->getDateTime($entity->getCreated()),
            'o:modified' => $entity->getModified() ? $this->getDateTime($entity->getModified()) : null,
        ];
    }

    public function adminUrl($action = null, $canonical = false)
    {
        $url = $this->getViewHelper('Url');

        return $url(
            'admin/alto/media-id',
            [
                'action' => $action,
                'media-id' => $this->media()->id(),
            ],
            ['force_canonical' => $canonical]
        );
    }

    public function media()
    {
        return $this->getAdapter('media')->getRepresentation($this->resource->getMedia());
    }

    public function xml(): ?string
    {
        $xml = $this->resource->getXml();
        if (isset($xml)) {
            return $xml;
        }

        $xmlCompressed = $this->resource->getXmlCompressed();
        if (isset($xmlCompressed)) {
            return gzdecode($xmlCompressed);
        }

        return null;
    }

    public function created()
    {
        return $this->resource->getCreated();
    }

    public function modified()
    {
        return $this->resource->getModified();
    }

    public function text()
    {
        return AltoReader::extractTextFromXml($this->xml());
    }
}
