<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

class ParticipateAction
{
    public function __construct(Template\TemplateRendererInterface $template = null)
    {
        $this->template = $template;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $page = $request->getAttribute('page', false);

        if (false === $page) {
            return new HtmlResponse($this->template->render("app::participate"));
        }

        if (! in_array($page, [ 'contributor-guide', 'contributors', 'logos' ])) {
            return new HtmlResponse($this->template->render('error::404'));
        }

        if ($page === 'contributor-guide') {
            return new HtmlResponse($this->template->render("app::$page", [
                'repository' => json_decode(file_get_contents('http://zendframework.github.io/zf-mkdoc-theme/scripts/zf-component-list.json'))
            ]));
        }
        return new HtmlResponse($this->template->render("app::$page"));
    }
}
