<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use App\Model\Changelog;

class ChangelogAction
{
    private $changelog;

    public function __construct(Changelog $changelog, Template\TemplateRendererInterface $template = null)
    {
        $this->changelog = $changelog;
        $this->template = $template;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $allChangelog = $this->changelog->getAll();
        $changelog = $request->getAttribute('changelog', basename(key($allChangelog), '.md'));

        $file = "data/changelog/$changelog.md";
        if (! $changelog || ! file_exists($file)) {
            return new HtmlResponse($this->template->render('error::404'));
        }
        $content = $this->changelog->getFromFile($file);
        $content['layout'] = 'layout::default';
        $content['changelog'] = $changelog;
        $content['versions'] = array_map(function($value){
            return basename($value, '.md');
        }, array_keys($allChangelog));
        return new HtmlResponse($this->template->render("app::changelog", $content));
    }
}
