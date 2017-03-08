<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use ArrayObject;

class AboutAction
{
    protected $config;

    protected $template;

    public function __construct(ArrayObject $config, Template\TemplateRendererInterface $template = null)
    {
        $this->config   = $config;
        $this->template = $template;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $page = basename($request->getUri()->getPath());
        $stat = $this->config['zf_stats']['total'] ?? false;

        return new HtmlResponse($this->template->render(sprintf('app::%s', $page), ['stats' => $stat]));
    }
}
