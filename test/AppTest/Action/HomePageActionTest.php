<?php

namespace AppTest\Action;

use App\Action\HomePageAction;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use App\Model\Post;
use App\Model\Advisory;
use Zend\Expressive\Template\TemplateRendererInterface;

class HomePageActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var RouterInterface */
    protected $router;

    /** @var Post */
    protected $post;

    /** @var Advisory */
    protected $advisory;

    protected function setUp()
    {
        $this->post = $this->getMockBuilder(Post::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $this->post->method('getAllPosts')
                   ->willReturn([]);

        $this->advisory = $this->getMockBuilder(Advisory::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $this->advisory->method('getAllAdvisories')
                       ->willReturn([]);

        $this->template = $this->getMockBuilder(TemplateRendererInterface::class)
                               ->getMock();
        $this->template->method('render')
                       ->willReturn('');
    }

    public function testResponse()
    {
        $homePage = new HomePageAction($this->post, $this->advisory, $this->template);
        $response = $homePage(new ServerRequest(['/']), new Response(), function () {
        });

        $this->assertTrue($response instanceof Response);
    }
}
