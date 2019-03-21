<?php
use App\Action;
use App\ApplicationErrorLogger;
use App\ApplicationErrorLoggerFactory;
use App\HostnameMiddleware;
use App\Model;
use Zend\Expressive\Application;
use Zend\Expressive\Container\ApplicationFactory;
use Zend\Expressive\Helper;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Router\ZendRouter;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    // Provides application-wide services.
    // We recommend using fully-qualified class names whenever possible as
    // service names.
    'dependencies' => [
        // Use 'invokables' for constructor-less services, or services that do
        // not require arguments to the constructor. Map a service name to the
        // class name.
        'invokables' => [
            // Fully\Qualified\InterfaceName::class => Fully\Qualified\ClassName::class,
            Helper\ServerUrlHelper::class => Helper\ServerUrlHelper::class,
            HostnameMiddleware::class => HostnameMiddleware::class,
            RouterInterface::class => ZendRouter::class,
        ],
        // Use 'factories' for services provided by callbacks/factory classes.
        'factories' => [
            Application::class => ApplicationFactory::class,
            Helper\UrlHelper::class => Helper\UrlHelperFactory::class,

            // Actions
            Action\HomePageAction::class     => Action\HomePageFactory::class,
            Action\ApiAction::class          => Action\ApiFactory::class,
            Action\BlogAction::class         => Action\BlogFactory::class,
            Action\AboutAction::class        => Action\AboutFactory::class,
            Action\AdvisoryAction::class     => Action\AdvisoryFactory::class,
            Action\SecurityAction::class     => Action\SecurityFactory::class,
            Action\ChangelogAction::class    => Action\ChangelogFactory::class,
            Action\IssueAction::class        => Action\IssueFactory::class,
            Action\ManualAction::class       => Action\ManualFactory::class,
            Action\LearnAction::class        => Action\LearnFactory::class,
            Action\DocsAction::class         => Action\DocsFactory::class,
            Action\SwitchManualAction::class => Action\SwitchManualFactory::class,
            Action\InstallAction::class      => Action\InstallFactory::class,
            Action\ParticipateAction::class  => Action\ParticipateFactory::class,
            Action\StatusAction::class       => Action\StatusFactory::class,
            Action\StatisticsAction::class   => Action\StatisticsFactory::class,

            // Models
            Model\Post::class      => Model\PostFactory::class,
            Model\Advisory::class  => Model\AdvisoryFactory::class,
            Model\Changelog::class => Model\ChangelogFactory::class,
            Model\Issue::class     => Model\IssueFactory::class,
            Model\Release::class   => Model\ReleaseFactory::class,

            // Middlewares
            Helper\ServerUrlMiddleware::class          => Helper\ServerUrlMiddlewareFactory::class,
            Helper\UrlHelperMiddleware::class          => Helper\UrlHelperMiddlewareFactory::class,
            Action\Redirects::class                    => InvokableFactory::class,
            Action\StripTrailingSlashMiddleware::class => InvokableFactory::class,
        ],
        'delegators' => [
            \Zend\Stratigility\Middleware\ErrorHandler::class => [
                ApplicationErrorLoggerFactory::class,
            ],
        ],
    ],
];
