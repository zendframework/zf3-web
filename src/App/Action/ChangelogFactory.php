<?php

namespace App\Action;

use App\Model\Changelog;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class ChangelogFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $changelog = $container->get(Changelog::class);
        $template  = $container->get(TemplateRendererInterface::class);

        return new ChangelogAction($changelog, $template);
    }
}
