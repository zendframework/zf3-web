<?php

namespace AppTest\Action;

use App\Action\HomePageAction;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use App\Model\Post;
use App\Model\Advisory;
use Zend\Expressive\Template\TemplateRendererInterface;
use ArrayObject;

class HomePageActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var RouterInterface */
    protected $router;

    /** @var Post */
    protected $post;

    /** @var Advisory */
    protected $advisory;

    private function getContent($body = false)
    {
        $post = [
            'layout' => 'layout',
            'title'  => 'title',
            'date'   => 1234828800,
        ];
        if ($body) {
            $post['body'] = 'body';
        }
        return $post;
    }
    protected function setUp()
    {
        $this->post = $this->getMockBuilder(Post::class)
                         ->disableOriginalConstructor()
                         ->getMock();

        $this->post->method('getAll')->willReturn([
            'foo' => $this->getContent()
        ]);
        $this->post->method('getFromFile')->willReturn($this->getContent(true));

        $this->advisory = $this->getMockBuilder(Advisory::class)
                               ->disableOriginalConstructor()
                               ->getMock();

        $this->advisory->method('getAll')->willReturn([
            'foo' => $this->getContent()
        ]);
        $this->advisory->method('getFromFile')->willReturn($this->getContent(true));

        $this->template = $this->getMockBuilder(TemplateRendererInterface::class)
                               ->getMock();
        $this->template->method('render')->willReturn('');

        $this->zfComponents = new ArrayObject([ 'zf_components' => [] ]);
    }

    public function testResponse()
    {
        $homePage = new HomePageAction($this->zfComponents, $this->post, $this->advisory, $this->template);
        $response = $homePage(new ServerRequest(['/']), new Response(), function () {
        });
        $this->assertTrue($response instanceof Response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
