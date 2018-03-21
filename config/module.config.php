<?php
return [
        'controllers' => [
                'invokables' => [
                        'RestrictedSites\Controller\Site\Index' => RestrictedSites\Controller\Site\IndexController::class,
                        'RestrictedSites\Controller\Site\Page' => \Omeka\Controller\Site\PageController::class
                ]
        ],
        'router' => [
                'routes' => [
                        'site' => [
                                'type' => 'Segment',
                                'options' => [
                                        'route' => '/s/:site-slug',
                                        'constraints' => [
                                                'site-slug' => '[a-zA-Z0-9_-]+'
                                        ],
                                        'defaults' => [
                                                '__NAMESPACE__' => 'RestrictedSites\Controller\Site',
                                                '__SITE__' => true,
                                                'controller' => 'Index',
                                                'action' => 'index'
                                        ]
                                ]
                        ]
                ]
        ]
]
;