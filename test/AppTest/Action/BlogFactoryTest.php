<?php

namespace AppTest\Action;

use App\Action\BlogAction;
use App\Action\BlogFactory;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use App\Model\Post;

class BlogFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContainerInterface */
    protected $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $post = $this->prophesize(Post::class);
        $this->container->get(Post::class)->willReturn($post);
    }

    public function testFactoryWithoutTemplate()
    {
        $factory = new BlogFactory();
        $this->container->has(TemplateRendererInterface::class)->willReturn(false);

        $this->assertTrue($factory instanceof BlogFactory);

        $homePage = $factory($this->container->reveal());

        $this->assertTrue($homePage instanceof BlogAction);
    }

    public function testFactoryWithTemplate()
    {
        $factory = new BlogFactory();
        $this->container->has(TemplateRendererInterface::class)->willReturn(true);
        $this->container
            ->get(TemplateRendererInterface::class)
            ->willReturn($this->prophesize(TemplateRendererInterface::class));

        $this->assertTrue($factory instanceof BlogFactory);

        $homePage = $factory($this->container->reveal());

        $this->assertTrue($homePage instanceof BlogAction);
    }
}
