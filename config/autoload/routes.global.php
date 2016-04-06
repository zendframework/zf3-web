<?php

return [
    'dependencies' => [
        'invokables' => [
            Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\ZendRouter::class,
        ],
        'factories' => [
            App\Action\HomePageAction::class => App\Action\HomePageFactory::class,
            App\Action\BlogAction::class => App\Action\BlogFactory::class,
            App\Action\AboutAction::class => App\Action\AboutFactory::class,
            App\Action\AdvisoryAction::class => App\Action\AdvisoryFactory::class,
            App\Action\SecurityAction::class => App\Action\SecurityFactory::class,
            App\Action\ChangelogAction::class => App\Action\ChangelogFactory::class,
        ],
    ],

    'routes' => [
        [
            'name' => 'home',
            'path' => '/',
            'middleware' => App\Action\HomePageAction::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'blog',
            'path' => '/blog[/:file]',
            'middleware' => App\Action\BlogAction::class
        ],
        [
            'name' => 'about',
            'path' => '/about[/faq]',
            'middleware' => App\Action\AboutAction::class
        ],
        [
            'name' => 'license',
            'path' => '/license',
            'middleware' => App\Action\AboutAction::class
        ],
        [
            'name' => 'long-term-support',
            'path' => '/long-term-support',
            'middleware' => App\Action\AboutAction::class
        ],
        [
            'name' => 'security',
            'path' => '/security[/:action]',
            'middleware' => App\Action\SecurityAction::class
        ],
        [
            'name' => 'advisory',
            'path' => '/security/advisory/:advisory',
            'middleware' => App\Action\AdvisoryAction::class
        ],
        [
            'name' => 'changelog',
            'path' => '/changelog[/:changelog]',
            'middleware' => App\Action\ChangelogAction::class
        ],
    ],
];
