<?php

namespace App\Action;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

class AboutAction implements RequestHandlerInterface
{
    /** @var array */
    private $config;

    /** @var Template\TemplateRendererInterface */
    private $template;

    public function __construct(array $config, Template\TemplateRendererInterface $template)
    {
        $this->config   = $config;
        $this->template = $template;
    }

    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        $page = basename($request->getUri()->getPath());
        $stat = $this->config['zf_stats']['total'] ?? false;

        return new HtmlResponse($this->template->render(sprintf('app::%s', $page), ['stats' => $stat]));
    }
}
