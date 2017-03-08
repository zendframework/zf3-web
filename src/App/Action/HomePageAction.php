<?php

namespace App\Action;

use App\Model;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

class HomePageAction implements MiddlewareInterface
{
    const NUM_POSTS = 5;
    const NUM_ADVISORIES = 4;

    /** @var array */
    private $config;

    /** @var Model\Post */
    private $posts;

    /** @var Model\Advisory */
    private $advisories;

    /** @var Template\TemplateRendererInterface */
    private $template;

    public function __construct(
        array $config,
        Model\Post $posts,
        Model\Advisory $advisories,
        Template\TemplateRendererInterface $template
    ) {
        $this->config     = $config;
        $this->posts      = $posts;
        $this->advisories = $advisories;
        $this->template   = $template;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $data = [
            'projects'   => require 'data/projects.php',
            'posts'      => array_slice($this->posts->getAll(), 0, self::NUM_POSTS),
            'advisories' => array_slice($this->advisories->getAll(), 0, self::NUM_ADVISORIES),
            'repository' => $this->config['zf_components'],
            'stats'      => $this->config['zf_stats']['total'] ?? false,
        ];
        $data['layout'] = 'layout::default';
        return new HtmlResponse($this->template->render('app::home-page', $data));
    }
}
