<?php

namespace App\Action;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class DocsFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $apidoc       = require 'config/autoload/zf-apidoc-versions.php';
        $zfComponents = $container->get('config')['zf_components'];
        $template     = $container->get(TemplateRendererInterface::class);

        return new DocsAction($apidoc, $zfComponents, $template);
    }
}
