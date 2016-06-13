<?php

namespace App\Action;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use App\Model\Post;
use App\Model\Advisory;

class HomePageFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $template = ($container->has(TemplateRendererInterface::class))
            ? $container->get(TemplateRendererInterface::class)
            : null;
        $post     = $container->get(Post::class);
        $advisory = $container->get(Advisory::class);
        $config   = $container->get('config');

        return new HomePageAction($config, $post, $advisory, $template);
    }
}
