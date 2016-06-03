<?php

namespace App\Action;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class ParticipateFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $template = ($container->has(TemplateRendererInterface::class))
            ? $container->get(TemplateRendererInterface::class)
            : null;
        $zfComponents = $container->get('config')['zf_components'];
        $reviewTeam   = $container->get('config')['zf-review-team'];
        
        return new ParticipateAction($zfComponents, $reviewTeam, $template);
    }
}
