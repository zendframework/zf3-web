<?php

namespace App;

class ApplicationErrorLoggerFactory
{
    public function __invoke()
    {
        $config = $container->get('config');
        $enabled = $config['api']['debug'] ?? false;
        $logPath = $config['log_path'] ?? '';
        return new ApplicationErrorLogger($enabled, $logPath);
    }
}
