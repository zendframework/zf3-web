<?php

namespace App\Action;

use App\Model\Post;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class BlogFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $post     = $container->get(Post::class);
        $template = $container->get(TemplateRendererInterface::class);

        return new BlogAction($post, $template);
    }
}
