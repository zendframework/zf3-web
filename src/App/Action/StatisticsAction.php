<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

class StatisticsAction implements RequestHandlerInterface
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

    public function handle(ServerRequestInterface $request) : ResponseInterface
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
