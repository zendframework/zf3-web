<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

class ParticipateAction implements MiddlewareInterface
{
    /** @var array */
    private $zfComponents;

    /** @var array */
    private $reviewTeam;

    /** @var Template\TemplateRendererInterface */
    private $template;

    public function __construct(
        array $zfComponents,
        array $reviewTeam,
        Template\TemplateRendererInterface $template
    ) {
        $this->zfComponents = $zfComponents;
        $this->reviewTeam   = $reviewTeam;
        $this->template     = $template;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $page = $request->getAttribute('page', false);

        if (false === $page) {
            return new HtmlResponse($this->template->render('app::participate'));
        }

        if (! in_array($page, ['contributor-guide', 'code-manifesto', 'contributors', 'logos'], true)) {
            return new HtmlResponse($this->template->render('error::404'));
        }

        if ($page === 'contributor-guide') {
            return new HtmlResponse($this->template->render(sprintf('app::%s', $page), [
                'repository' => $this->zfComponents,
            ]));
        }

        return new HtmlResponse($this->template->render(sprintf('app::%s', $page), [
            'reviewTeam' => $this->reviewTeam,
        ]));
    }
}
