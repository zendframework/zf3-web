<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

class LearnAction implements MiddlewareInterface
{
    /** @var array */
    private $zfComponents;

    /** @var Template\TemplateRendererInterface */
    private $template;

    public function __construct(array $zfComponents, Template\TemplateRendererInterface $template)
    {
        $this->zfComponents = $zfComponents;
        $this->template     = $template;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $page = $request->getAttribute('page', false);

        if (false === $page) {
            return new HtmlResponse($this->template->render('app::learn', ['components' => $this->zfComponents]));
        }

        if (! in_array($page, ['learn', 'training-and-certification', 'support-and-consulting'], true)) {
            return new HtmlResponse($this->template->render('error::404'));
        }

        return new HtmlResponse($this->template->render(sprintf('app::%s', $page)));
    }
}
