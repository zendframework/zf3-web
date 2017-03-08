<?php

namespace App\Action;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class ParticipateFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $template = $container->get(TemplateRendererInterface::class);
        $zfComponents = $container->get('config')['zf_components'];
        $reviewTeam   = $container->get('config')['zf-review-team'];

        return new ParticipateAction($zfComponents, $reviewTeam, $template);
    }
}
