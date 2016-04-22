<?php

namespace App\Action;

use Interop\Container\ContainerInterface;

class SwitchManualFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        return new SwitchManualAction($config['manual']);
    }
}
