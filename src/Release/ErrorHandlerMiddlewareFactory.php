<?php

namespace Release;

use Psr\Container\ContainerInterface;

class ErrorHandlerMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : ErrorHandlerMiddleware
    {
        $config = $container->get('config');
        $enabled = $config['api']['debug'] ?? false;
        return new ErrorHandlerMiddleware($enabled);
    }
}
