<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

class DocsAction implements RequestHandlerInterface
{
    /** @var array */
    private $apidoc;

    /** @var array */
    private $zfComponents;

    /** @var Template\TemplateRendererInterface */
    private $template;

    public function __construct(array $apidoc, array $zfComponents, Template\TemplateRendererInterface $template)
    {
        $this->apidoc       = $apidoc;
        $this->zfComponents = $zfComponents;
        $this->template     = $template;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $ver = $request->getAttribute('ver', false);

        if (false === $ver) {
            return new HtmlResponse($this->template->render('app::learn', ['components' => $this->zfComponents]));
        }
        $ver = (int) substr($ver, 2);
        return new HtmlResponse($this->template->render('app::api', ['zf' => $ver, 'versions' => $this->apidoc]));
    }
}
