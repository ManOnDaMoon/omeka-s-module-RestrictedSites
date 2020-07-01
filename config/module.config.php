<?php
return [
        'view_manager' => [
                'template_path_stack' => [
                        OMEKA_PATH . '/modules/RestrictedSites/view'
                ]
        ],
        'controllers' => [
                'factories' => [
                        'RestrictedSites\Controller\Site\SiteLogin' => RestrictedSites\Service\Controller\Site\SiteLoginControllerFactory::class,
                        'RestrictedSites\Stdlib\SiteMailer' => RestrictedSites\Service\SiteMailerFactory::class,
                ]
        ],
        'form_elements' => [
            'factories' => [
                'RestrictedSites\Form\ConfigForm' => 'RestrictedSites\Service\Form\ConfigFormFactory',
                'RestrictedSites\Form\SiteLoginForm' => 'RestrictedSites\Service\Form\SiteLoginFormFactory',
            ],
        ],
        'service_manager' => [
            'factories' => [
                'RestricedSites\SiteMailer' => RestrictedSites\Service\SiteMailerFactory::class,
                'Omeka\Mailer' => RestrictedSites\Service\SiteMailerFactory::class, //required to customize user creation mail
            ]
        ],
        'controller_plugins' => [
            'factories' => [
                // Specific plugin in case I manage to NOT override core Mailer service:
                'sitemailer' => RestrictedSites\Service\ControllerPlugin\SiteMailerFactory::class,
            ],
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
                                'type' => \Laminas\Router\Http\Segment::class,
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
                                        'type' => \Laminas\Router\Http\Segment::class,
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
                                    'create-password' => [
                                        'type' => \Laminas\Router\Http\Segment::class,
                                        'options' => [
                                            'route' => '/create-password/:key',
                                            'constraints' => [
                                                'key' => '[a-zA-Z0-9]+',
                                            ],
                                            'defaults' => [
                                                'action' => 'create-password',
                                            ],
                                            'constraints' => [
                                                'controller' => 'SiteLogin',
                                                'action' => 'createPassword',
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
