<?php

namespace App\Action;

use App\Model\Advisory;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class SecurityFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $advisory = $container->get(Advisory::class);
        $template = $container->get(TemplateRendererInterface::class);

        return new SecurityAction($advisory, $template);
    }
}
