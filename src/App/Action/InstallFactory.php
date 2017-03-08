<?php

namespace App\Action;

use App\Model\Release;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class InstallFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $release  = $container->get(Release::class);
        $template = $container->get(TemplateRendererInterface::class);

        return new InstallAction($release, $template);
    }
}
