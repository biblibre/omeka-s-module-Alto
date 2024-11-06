<?php

namespace Alto;

return [
    'api_adapters' => [
        'invokables' => [
            'alto_documents' => Api\Adapter\AltoDocumentAdapter::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
            'Alto\Controller\Admin\Media' => Controller\Admin\MediaController::class,
        ],
        'factories' => [
            'Alto\Controller\Admin\Alto' => Service\Controller\Admin\AltoControllerFactory::class,
        ],
    ],
    'entity_manager' => [
        'mapping_classes_paths' => [
            dirname(__DIR__) . '/src/Entity',
        ],
        'proxy_paths' => [
            dirname(__DIR__) . '/data/doctrine-proxies',
        ],
    ],
    'navigation' => [
        'AdminModule' => [
            [
                'label' => 'ALTO import', // @translate
                'class' => 'alto-import',
                'route' => 'admin/alto/import',
                'resource' => 'Alto\Controller\Admin\Alto',
                'privilege' => 'import',
            ],
        ],
    ],
    'resource_page_block_layouts' => [
        'invokables' => [
            'altoText' => Site\ResourcePageBlockLayout\AltoText::class,
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'alto' => [
                        'type' => \Laminas\Router\Http\Literal::class,
                        'options' => [
                            'route' => '/alto',
                            'defaults' => [
                                '__NAMESPACE__' => 'Alto\Controller\Admin',
                            ],
                        ],
                        'child_routes' => [
                            'media-id' => [
                                'type' => \Laminas\Router\Http\Segment::class,
                                'options' => [
                                    'route' => '/media/:media-id/:action',
                                    'defaults' => [
                                        'controller' => 'media',
                                    ],
                                ],
                            ],
                            'import' => [
                                'type' => \Laminas\Router\Http\Literal::class,
                                'options' => [
                                    'route' => '/import',
                                    'defaults' => [
                                        'controller' => 'alto',
                                        'action' => 'import',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => dirname(__DIR__) . '/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
];
