<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use App\Model\Issue;

class IssueAction
{
    const ISSUE_PER_PAGE = 15;

    private $issue;

    public function __construct(Issue $issue, array $zfComponents, Template\TemplateRendererInterface $template = null)
    {
        $this->issue        = $issue;
        $this->zfComponents = $zfComponents;
        $this->template     = $template;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $action = $request->getAttribute('type', false);
        if (! $action) {
            return new HtmlResponse($this->template->render("app::issue-overview", [
                'active'     => '/issues',
                'repository' => $this->zfComponents
            ]));
        }

        if (! in_array($action, [ 'ZF1', 'ZF2', 'browse'])) {
            return new HtmlResponse($this->template->render('error::404'));
        }

        return $this->$action($request, $response, $next);
    }

    protected function browse(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $issueId = $request->getAttribute('issue', false);
        if (! $issueId) {
            return new HtmlResponse($this->template->render('error::404'));
        }

        $file = 'data/issues/' . $issueId . '.md';
        if (! file_exists($file)) {
            return new HtmlResponse($this->template->render('error::404'));
        }

        $content               = $this->issue->getFromFile($file);
        $content['layout']     = 'layout::default';
        $content['active']     = strpos($issueId, 'ZF-') !== false ? '/issues/ZF1' : '/issues/ZF2';
        $content['repository'] = $this->zfComponents;

        return new HtmlResponse($this->template->render('app::issue', $content));
    }

    protected function zf1(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        return $this->zf2($request, $response, $next);
    }

    protected function zf2(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $action = $request->getAttribute('type', false);

        $allIssues = $this->issue->getAll()[$action];
        $totIssues = count($allIssues);

        $params = $request->getQueryParams();
        $page = isset($params['page']) ? (int) $params['page'] : 1;

        $totPages = ceil($totIssues / self::ISSUE_PER_PAGE);

        if ($page > $totPages || $page < 1) {
            return new HtmlResponse($this->template->render('error::404'));
        }

        $nextPage = ($page === $totPages) ? 0 : $page + 1;
        $prevPage = ($page === 1) ? 0 : $page - 1;

        $active = "/issues/$action";
        $issues = array_slice($allIssues, ($page - 1) * self::ISSUE_PER_PAGE, self::ISSUE_PER_PAGE);
        return new HtmlResponse($this->template->render('app::zf-issue', [
            'issues'     => $issues,
            'ver'        => $action,
            'tot_issue'  => $totIssues,
            'tot'        => $totPages,
            'page'       => $page,
            'prev'       => $prevPage,
            'next'       => $nextPage,
            'active'     => $active,
            'repository' => $this->zfComponents
        ]));
    }
}
