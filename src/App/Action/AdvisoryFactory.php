<?php

namespace App\Action;

use App\Model\Advisory;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class AdvisoryFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $advisory = $container->get(Advisory::class);
        $template = $container->get(TemplateRendererInterface::class);

        return new AdvisoryAction($advisory, $template);
    }
}
