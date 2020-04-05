<?php
return [
        'view_manager' => [
                'template_path_stack' => [
                        OMEKA_PATH . '/modules/RestrictedSites/view'
                ]
        ],
        'controllers' => [
                'factories' => [
                        'RestrictedSites\Controller\Site\SiteLogin' => RestrictedSites\Service\Controller\Site\SiteLoginControllerFactory::class
                ]
        ],
        'navigation_links' => [
                'invokables' => [
                    'RestrictedSites\Site\Navigation\Link\Logout' => RestrictedSites\Site\Navigation\Link\Logout::class
                    ],
        ],
        'translator' => [
                'translation_file_patterns' => [
                        [
                            'type' => 'gettext',
                            'base_dir' => OMEKA_PATH . '/modules/RestrictedSites/language',
                            'pattern' => '%s.mo',
                            'text_domain' => null,
                        ],
                ],
        ],
        'router' => [
                'routes' => [
                        'sitelogin' => [
                                'type' => \Zend\Router\Http\Segment::class,
                                'options' => [
                                        'route' => '/sitelogin/:site-slug',
                                        'constraints' => [
                                                'site-slug' => '[a-zA-Z0-9_-]+'
                                        ],
                                        'defaults' => [
                                                '__NAMESPACE__' => 'RestrictedSites\Controller\Site',
                                                '__SITE__' => true,
                                                'controller' => 'SiteLogin',
                                                'action' => 'login'
                                        ]
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'forgot-password' => [
                                        'type' => \Zend\Router\Http\Segment::class,
                                        'options' => [
                                            'route' => '/forgot-password',
                                            'defaults' => [
                                                'action' => 'forgot-password',
                                            ],
                                            'constraints' => [
                                                'controller' => 'SiteLogin',
                                                'action' => 'forgotPassword',
                                            ],
                                        ],
                                    ],
                                ],
                        ],
                        'sitelogout' => [
                                'type' => 'Segment',
                                'options' => [
                                        'route' => '/sitelogout/:site-slug',
                                        'constraints' => [
                                            'site-slug' => '[a-zA-Z0-9_-]+'
                                        ],
                                        'defaults' => [
                                                '__NAMESPACE__' => 'RestrictedSites\Controller\Site',
                                                '__SITE__' => true,
                                                'controller' => 'SiteLogin',
                                                'action' => 'logout'
                            ],
                        ],
                    ],
                ],
        ],
];

