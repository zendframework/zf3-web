<?php

namespace App\Action;

use App\Model\Advisory;
use App\Model\Post;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class HomePageFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config   = $container->get('config');
        $post     = $container->get(Post::class);
        $advisory = $container->get(Advisory::class);
        $template = $container->get(TemplateRendererInterface::class);

        return new HomePageAction($config, $post, $advisory, $template);
    }
}
