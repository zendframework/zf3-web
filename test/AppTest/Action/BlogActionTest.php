<?php

namespace AppTest\Action;

use App\Action\BlogAction;
use App\Model\Post;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Template\TemplateRendererInterface;

class BlogActionTest extends TestCase
{
    /** @var Post|ObjectProphecy */
    private $post;

    /** @var TemplateRendererInterface|ObjectProphecy */
    private $template;

    protected function setUp()
    {
        $this->post = $this->prophesize(Post::class);
        $this->post->getAll()->willReturn([]);

        $this->template = $this->prophesize(TemplateRendererInterface::class);
        $this->template->render('error::404')->willReturn('');
    }

    public function testAllPosts()
    {
        $blogPage = new BlogAction($this->post->reveal(), $this->template->reveal());
        $response = $blogPage->handle(new ServerRequest(['/blog']));
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(StatusCode::STATUS_OK, $response->getStatusCode());
    }

    public function testPost()
    {
        $blogPage = new BlogAction($this->post->reveal(), $this->template->reveal());
        $response = $blogPage->handle(new ServerRequest(['/blog/koo']));
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(StatusCode::STATUS_OK, $response->getStatusCode());
    }
}
