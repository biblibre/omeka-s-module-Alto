<?php
namespace Alto\Site\ResourcePageBlockLayout;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Site\ResourcePageBlockLayout\ResourcePageBlockLayoutInterface;
use Laminas\View\Renderer\PhpRenderer;

class AltoText implements ResourcePageBlockLayoutInterface
{
    public function getLabel() : string
    {
        return 'ALTO text content'; // @translate
    }

    public function getCompatibleResourceNames() : array
    {
        return ['media'];
    }

    public function render(PhpRenderer $view, AbstractResourceEntityRepresentation $resource) : string
    {
        return $view->partial('alto/common/resource-page-block-layout/alto-text', ['media' => $resource]);
    }
}
