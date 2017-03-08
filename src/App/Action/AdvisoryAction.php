<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use App\Model\Advisory;

class AdvisoryAction
{
    private $advisory;

    public function __construct(Advisory $advisory, Template\TemplateRendererInterface $template = null)
    {
        $this->advisory = $advisory;
        $this->template = $template;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $advisory = $request->getAttribute('advisory', false);
        $file = sprintf('data/advisories/%s.md', basename($advisory));
        if (! $advisory || ! file_exists($file)) {
            return new HtmlResponse($this->template->render('error::404'));
        }
        $content = $this->advisory->getFromFile($file);
        $content['layout'] = 'layout::default';
        $content['advisory'] = $advisory;

        return new HtmlResponse($this->template->render('app::advisory', $content));
    }
}
