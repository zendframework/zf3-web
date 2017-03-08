<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

class AboutAction implements MiddlewareInterface
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

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $page = basename($request->getUri()->getPath());
        $stat = $this->config['zf_stats']['total'] ?? false;

        return new HtmlResponse($this->template->render(sprintf('app::%s', $page), ['stats' => $stat]));
    }
}
