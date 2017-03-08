<?php

namespace App\Action;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class StatisticsFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config   = $container->get('config');
        $template = $container->get(TemplateRendererInterface::class);

        return new StatisticsAction($config, $template);
    }
}
