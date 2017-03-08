<?php

namespace App\Action;

use App\Model\Advisory;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

class AdvisoryAction implements MiddlewareInterface
{
    /** @var Advisory */
    private $advisory;

    /** @var Template\TemplateRendererInterface */
    private $template;

    public function __construct(Advisory $advisory, Template\TemplateRendererInterface $template)
    {
        $this->advisory = $advisory;
        $this->template = $template;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
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
