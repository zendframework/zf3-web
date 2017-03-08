<?php

namespace AppTest\Action;

use App\Action\BlogAction;
use App\Action\BlogFactory;
use App\Model\Post;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class BlogFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $post = $this->prophesize(Post::class);
        $this->container->get(Post::class)->willReturn($post);
    }

    public function testFactoryWithTemplate()
    {
        $factory = new BlogFactory();
        $this->container
            ->get(TemplateRendererInterface::class)
            ->willReturn($this->prophesize(TemplateRendererInterface::class));

        $homePage = $factory($this->container->reveal());

        $this->assertInstanceOf(BlogAction::class, $homePage);
    }
}
