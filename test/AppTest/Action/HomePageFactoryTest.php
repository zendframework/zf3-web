<?php

namespace AppTest\Action;

use App\Action\HomePageAction;
use App\Action\HomePageFactory;
use App\Model\Advisory;
use App\Model\Post;
use ArrayObject;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class HomePageFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $post = $this->prophesize(Post::class);
        $advisory = $this->prophesize(Advisory::class);

        $this->container->get(Post::class)->willReturn($post);
        $this->container->get(Advisory::class)->willReturn($advisory);
        $this->container->get('config')->willReturn(new ArrayObject(['zf_components' => []]));
    }

    public function testFactoryWithTemplate()
    {
        $factory = new HomePageFactory();
        $this->container
            ->get(TemplateRendererInterface::class)
            ->willReturn($this->prophesize(TemplateRendererInterface::class));

        $homePage = $factory($this->container->reveal());
        $this->assertInstanceOf(HomePageAction::class, $homePage);
    }
}
