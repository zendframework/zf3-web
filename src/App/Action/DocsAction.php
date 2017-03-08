<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

class DocsAction
{
    public function __construct(array $apidoc, array $zfComponents, Template\TemplateRendererInterface $template = null)
    {
        $this->template     = $template;
        $this->apidoc       = $apidoc;
        $this->zfComponents = $zfComponents;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $ver = $request->getAttribute('ver', false);

        if (false === $ver) {
            return new HtmlResponse($this->template->render('app::learn', ['components' => $this->zfComponents]));
        }
        $ver = (int) substr($ver, 2);
        return new HtmlResponse($this->template->render('app::api', ['zf' => $ver, 'versions' => $this->apidoc]));
    }
}
