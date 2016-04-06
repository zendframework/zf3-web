<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use App\Model\Advisory;

class SecurityAction
{
    const ADVISORY_PER_PAGE = 10;

    private $advisory;

    public function __construct(Advisory $advisory, Template\TemplateRendererInterface $template = null)
    {
        $this->advisory = $advisory;
        $this->template = $template;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $action = $request->getAttribute('action', 'security');
        if (! file_exists("templates/app/$action.phtml")) {
            return new HtmlResponse($this->template->render('error::404'));
        }
        $content = [];
        if ('advisories' === $action) {
            $params = $request->getQueryParams();
            $page = isset($params['page']) ? (int) $params['page'] : 1;

            $allAdvisories = $this->advisory->getAllAdvisories();
            $totPages = ceil(count($allAdvisories) / self::ADVISORY_PER_PAGE);

            if ($page > $totPages || $page < 1) {
              return new HtmlResponse($this->template->render('error::404'));
            }
            $nextPage = ($page === $totPages) ? 0 : $page + 1;
            $prevPage = ($page === 1) ? 0 : $page - 1;

            $advisories = array_slice($allAdvisories, ($page - 1) * self::ADVISORY_PER_PAGE, self::ADVISORY_PER_PAGE);
            $content = [
              'advisories' => $advisories,
              'tot'        => $totPages,
              'page'       => $page,
              'prev'       => $prevPage,
              'next'       => $nextPage
            ];
        }
        return new HtmlResponse($this->template->render("app::$action", $content));
    }
}
