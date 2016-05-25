<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use App\Model\Release;

class InstallAction
{
    public function __construct(Release $release, Template\TemplateRendererInterface $template = null)
    {
        $this->template = $template;
        $this->releases = $release;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $page = $request->getAttribute('page', false);

        if (false === $page) {
            $url = 'https://raw.githubusercontent.com/zendframework/zendframework/master/composer.json';
            $composer = file_get_contents($url);
            if (false !== $composer) {
                $composer = json_decode($composer, true);
            }
            return new HtmlResponse($this->template->render("app::install", [
                'require'     => $composer['require'],
                'composerUrl' => $url
            ]));
        }

        if (! in_array($page, [ 'skeleton-app', 'expressive', 'archives' ])) {
            return new HtmlResponse($this->template->render('error::404'));
        }

        if ($page === 'archives') {
            return new HtmlResponse($this->template->render("app::$page", [ 'releases' => $this->releases ]));
        }
        return new HtmlResponse($this->template->render("app::$page"));
    }
}
