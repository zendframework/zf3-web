<?php

namespace App\Action;

use App\Model\Issue;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class IssueFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $issue        = $container->get(Issue::class);
        $zfComponents = $container->get('config')['zf_components'];
        $template     = $container->get(TemplateRendererInterface::class);

        return new IssueAction($issue, $zfComponents, $template);
    }
}
