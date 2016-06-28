<?php

namespace App\Action;

use Interop\Container\ContainerInterface;

class ApiFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $config = $config['api']['zf-version'] ?? [];

        return new ApiAction($config);
    }
}
