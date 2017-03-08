<?php

namespace App\Action;

use ArrayObject;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

class StatisticsAction implements MiddlewareInterface
{
    /** @var ArrayObject */
    private $config;

    /** @var Template\TemplateRendererInterface */
    private $template;

    public function __construct(ArrayObject $config, Template\TemplateRendererInterface $template)
    {
        $this->config   = $config;
        $this->template = $template;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (! isset($this->config['zf_stats'])) {
            return new HtmlResponse($this->template->render('error::404'));
        }
        $stats = $this->config['zf_stats'];
        uasort($stats, function ($a, $b) {
            return ($a['total'] <=> $b['total']) * -1;
        });
        return new HtmlResponse($this->template->render('app::statistics', ['stats' => $stats]));
    }
}
