<?php

return [
    'dependencies' => [
        'invokables' => [
            Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\ZendRouter::class
        ],
        'factories' => [
            App\Action\HomePageAction::class     => App\Action\HomePageFactory::class,
            App\Action\BlogAction::class         => App\Action\BlogFactory::class,
            App\Action\AboutAction::class        => App\Action\AboutFactory::class,
            App\Action\AdvisoryAction::class     => App\Action\AdvisoryFactory::class,
            App\Action\SecurityAction::class     => App\Action\SecurityFactory::class,
            App\Action\ChangelogAction::class    => App\Action\ChangelogFactory::class,
            App\Action\IssueAction::class        => App\Action\IssueFactory::class,
            App\Action\ManualAction::class       => App\Action\ManualFactory::class,
            App\Action\LearnAction::class        => App\Action\LearnFactory::class,
            App\Action\DocsAction::class         => App\Action\DocsFactory::class,
            App\Action\SwitchManualAction::class => App\Action\SwitchManualFactory::class,
            App\Action\InstallAction::class      => App\Action\InstallFactory::class,
            App\Action\ParticipateAction::class  => App\Action\ParticipateFactory::class,
            App\Action\StatusAction::class       => App\Action\StatusFactory::class,
            App\Action\StatisticsAction::class   => App\Action\StatisticsFactory::class,
        ],
    ],

    'routes' => [
        [
            'name' => 'home',
            'path' => '/',
            'middleware' => App\Action\HomePageAction::class,
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'blog',
            'path' => '/blog[/:file]',
            'middleware' => App\Action\BlogAction::class,
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'about',
            'path' => '/about[/faq]',
            'middleware' => App\Action\AboutAction::class,
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'license',
            'path' => '/license',
            'middleware' => App\Action\AboutAction::class,
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'long-term-support',
            'path' => '/long-term-support',
            'middleware' => App\Action\AboutAction::class,
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'security',
            'path' => '/security[/:action]',
            'middleware' => App\Action\SecurityAction::class,
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'advisory',
            'path' => '/security/advisory/:advisory',
            'middleware' => App\Action\AdvisoryAction::class,
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'changelog',
            'path' => '/changelog[/:changelog]',
            'middleware' => App\Action\ChangelogAction::class,
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'issue',
            'path' => '/issues[/:type[/:issue]]',
            'middleware' => App\Action\IssueAction::class,
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'manual',
            'path' => '/manual/:version/:lang/:page[/:subpage]',
            'middleware' => App\Action\ManualAction::class,
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'switch-manual',
            'path' => '/switch-manual',
            'middleware' => App\Action\SwitchManualAction::class,
            'allowed_methods' => ['POST']
        ],
        [
            'name' => 'learn',
            'path' => '/learn[/:page]',
            'middleware' => App\Action\LearnAction::class,
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'docs',
            'path' => '/docs[/api/:ver]',
            'middleware' => App\Action\DocsAction::class,
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'install',
            'path' => '/downloads[/:page]',
            'middleware' => App\Action\InstallAction::class,
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'participate',
            'path' => '/participate[/:page]',
            'middleware' => App\Action\ParticipateAction::class,
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'status',
            'path' => '/status',
            'middleware' => App\Action\StatusAction::class,
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'statistics',
            'path' => '/stats',
            'middleware' => App\Action\StatisticsAction::class,
            'allowed_methods' => ['GET']
        ]
    ],
];
