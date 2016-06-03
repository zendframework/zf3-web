<?php

namespace App\Action;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use App\Model\Issue;

class IssueFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $template = ($container->has(TemplateRendererInterface::class))
            ? $container->get(TemplateRendererInterface::class)
            : null;
        $issue        = $container->get(Issue::class);
        $zfComponents = $container->get('config')['zf_components'];

        return new IssueAction($issue, $zfComponents, $template);
    }
}
