<?php

namespace App\Action;

use Psr\Container\ContainerInterface;

class SwitchManualFactory
{
    use PrepareManualConfigurationTrait;

    public function __invoke(ContainerInterface $container)
    {
        $config = $this->prepareManualConfiguration($container->get('config'));
        return new SwitchManualAction($config);
    }
}
