<?php

declare(strict_types=1);

/**
 * Expressive middleware pipeline
 */

return function (
    \Zend\Expressive\Application $app,
    \Zend\Expressive\MiddlewareFactory $factory,
    \Psr\Container\ContainerInterface $container
) : void {
    $app->pipe(\Zend\Stratigility\Middleware\OriginalMessages::class);
    $app->pipe(\Zend\Stratigility\Middleware\ErrorHandler::class);
    $app->pipe(\App\Action\StripTrailingSlashMiddleware::class);
    $app->pipe(\App\Action\Redirects::class);
    $app->pipe(\Zend\Expressive\Helper\ServerUrlMiddleware::class);
    $app->pipe(\Zend\Expressive\Router\Middleware\RouteMiddleware::class);
    $app->pipe(\Zend\Expressive\Router\Middleware\ImplicitHeadMiddleware::class);
    $app->pipe(\Zend\Expressive\Router\Middleware\ImplicitOptionsMiddleware::class);
    $app->pipe(\Zend\Expressive\Router\Middleware\MethodNotAllowedMiddleware::class);
    $app->pipe(\Zend\Expressive\Helper\UrlHelperMiddleware::class);
    $app->pipe(\Zend\Expressive\Router\Middleware\DispatchMiddleware::class);
    $app->pipe(\Zend\Expressive\Handler\NotFoundHandler::class);
};
