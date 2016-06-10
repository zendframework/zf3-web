<?php

namespace AppTest\Action;

use App\Action\BlogAction;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use App\Model\Post;
use Zend\Expressive\Template\TemplateRendererInterface;

class BlogActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var Post */
    protected $post;

    protected function setUp()
    {
        $this->post = $this->getMockBuilder(Post::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $this->post->method('getAll')
                   ->willReturn([]);

        $this->template = $this->getMockBuilder(TemplateRendererInterface::class)
                               ->getMock();
        $this->template->method('render')
                       ->willReturn('');
    }

    public function testAllPosts()
    {
        $blogPage = new BlogAction($this->post, $this->template);
        $response = $blogPage(new ServerRequest(['/blog']), new Response(), function () {
        });
        $this->assertTrue($response instanceof Response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPost()
    {
        $blogPage = new BlogAction($this->post, $this->template);
        $response = $blogPage(new ServerRequest(['/blog/koo']), new Response(), function () {
        });
        $this->assertTrue($response instanceof Response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
