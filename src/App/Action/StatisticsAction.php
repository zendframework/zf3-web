<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use ArrayObject;

class StatisticsAction
{
    private $template;

    private $config;

    public function __construct(ArrayObject $config, Template\TemplateRendererInterface $template = null)
    {
        $this->config   = $config;
        $this->template = $template;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        if (!isset($this->config['zf_stats'])) {
            return new HtmlResponse($this->template->render('error::404'));
        }
        $stats = $this->config['zf_stats'];
        uasort($stats, function($a, $b){
            return ($a['total'] <=> $b['total']) * -1;
        });
        return new HtmlResponse($this->template->render('app::statistics', [ 'stats' => $stats ]));
    }
}
