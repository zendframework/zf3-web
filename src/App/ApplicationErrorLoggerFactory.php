<?php

namespace App;

use Psr\Container\ContainerInterface;

class ApplicationErrorLoggerFactory
{
    public function __invoke(ContainerInterface $container, $serviceName, callable $callback)
    {
        $config = $container->get('config');
        $enabled = $config['api']['debug'] ?? false;
        $logPath = $config['log_path'] ?? '';
        $listener = new ApplicationErrorLogger($enabled, $logPath);

        $errorHandler = $callback();
        $errorHandler->attachListener($listener);
        return $errorHandler;
    }
}
