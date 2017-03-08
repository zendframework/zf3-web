<?php

namespace App\Action;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class LearnFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $zfComponents = $container->get('config')['zf_components'];
        $template     = $container->get(TemplateRendererInterface::class);

        return new LearnAction($zfComponents, $template);
    }
}
