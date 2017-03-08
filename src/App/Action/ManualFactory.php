<?php

namespace App\Action;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class ManualFactory
{
    use PrepareManualConfigurationTrait;

    public function __invoke(ContainerInterface $container)
    {
        $config   = $this->prepareManualConfiguration($container->get('config'));
        $template = $container->get(TemplateRendererInterface::class);

        return new ManualAction($config, $template);
    }
}
