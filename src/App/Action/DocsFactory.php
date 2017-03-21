<?php

namespace App\Action;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class DocsFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config       = $container->get('config');
        $apidoc       = $config['zf_apidoc_versions'];
        $zfComponents = $config['zf_components'];
        $template     = $container->get(TemplateRendererInterface::class);

        return new DocsAction($apidoc, $zfComponents, $template);
    }
}
