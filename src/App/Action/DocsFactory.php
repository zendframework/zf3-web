<?php

namespace App\Action;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class DocsFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $template = ($container->has(TemplateRendererInterface::class))
            ? $container->get(TemplateRendererInterface::class)
            : null;
        $apidoc       = require 'config/autoload/zf-apidoc-versions.php';
        $zfComponents = $containger->get('config')['zf_components'];
        return new DocsAction($apidoc, $zfComponents, $template);
    }
}
