<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

class InstallAction
{
    public function __construct(Template\TemplateRendererInterface $template = null)
    {
        $this->template = $template;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $page = $request->getAttribute('page', false);

        if (false === $page) {
            return new HtmlResponse($this->template->render("app::install"));
        }

        if (! in_array($page, [ 'skeleton-app', 'expressive', 'archives' ])) {
            return new HtmlResponse($this->template->render('error::404'));
        }

        return new HtmlResponse($this->template->render("app::$page"));
    }
}
