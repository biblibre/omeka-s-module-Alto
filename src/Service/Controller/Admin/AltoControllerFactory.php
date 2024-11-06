<?php

namespace Alto\Service\Controller\Admin;

use Alto\Controller\Admin\AltoController;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AltoControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $tempFileFactory = $services->get('Omeka\File\TempFileFactory');

        $controller = new AltoController($tempFileFactory);

        return $controller;
    }
}
