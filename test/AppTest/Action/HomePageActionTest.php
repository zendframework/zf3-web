<?php

namespace AppTest\Action;

use App\Action\HomePageAction;
use App\Model\Advisory;
use App\Model\Post;
use ArrayObject;
use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Template\TemplateRendererInterface;

class HomePageActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var Post|ObjectProphecy */
    private $post;

    /** @var Advisory|ObjectProphecy */
    private $advisory;

    /** @var TemplateRendererInterface|ObjectProphecy */
    private $template;

    /** @var ArrayObject */
    private $zfComponents;

    protected function setUp()
    {
        $this->post = $this->prophesize(Post::class);
        $this->post->getAll()->willReturn(['foo' => $this->getContent()])->shouldBeCalledTimes(1);

        $this->advisory = $this->prophesize(Advisory::class);
        $this->advisory->getAll()->willReturn(['foo' => $this->getContent()])->shouldBeCalledTimes(1);

        $this->template = $this->prophesize(TemplateRendererInterface::class);
        $this->template->render('app::home-page', Argument::type('array'))->willReturn('');

        $this->zfComponents = new ArrayObject(['zf_components' => []]);
    }

    public function testResponse()
    {
        $delegate = $this->prophesize(DelegateInterface::class);
        $homePage = new HomePageAction(
            $this->zfComponents,
            $this->post->reveal(),
            $this->advisory->reveal(),
            $this->template->reveal()
        );
        $response = $homePage->process(new ServerRequest(['/']), $delegate->reveal());
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(StatusCode::STATUS_OK, $response->getStatusCode());
    }

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
}
