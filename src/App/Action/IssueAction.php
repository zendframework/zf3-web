<?php

namespace App\Action;

use App\Model\Issue;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

class IssueAction implements RequestHandlerInterface
{
    const ISSUE_PER_PAGE = 15;

    /** @var Issue */
    private $issue;

    /** @var array */
    private $zfComponents;

    /** @var Template\TemplateRendererInterface */
    private $template;

    public function __construct(Issue $issue, array $zfComponents, Template\TemplateRendererInterface $template)
    {
        $this->issue        = $issue;
        $this->zfComponents = $zfComponents;
        $this->template     = $template;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $action = $request->getAttribute('type', false);
        if (! $action) {
            return new HtmlResponse($this->template->render('app::issue-overview', [
                'active'     => '/issues',
                'repository' => $this->zfComponents,
            ]));
        }

        if (! in_array($action, ['ZF1', 'ZF2', 'browse'], true)) {
            return new HtmlResponse($this->template->render('error::404'));
        }

        return $this->$action($request);
    }

    protected function browse(ServerRequestInterface $request)
    {
        $issueId = $request->getAttribute('issue', false);
        if (! $issueId) {
            return new HtmlResponse($this->template->render('error::404'));
        }

        $file = sprintf('data/issues/%s.md', $issueId);
        if (! file_exists($file)) {
            return new HtmlResponse($this->template->render('error::404'));
        }

        $content               = $this->issue->getFromFile($file);
        $content['layout']     = 'layout::default';
        $content['active']     = strpos($issueId, 'ZF-') !== false ? '/issues/ZF1' : '/issues/ZF2';
        $content['repository'] = $this->zfComponents;

        return new HtmlResponse($this->template->render('app::issue', $content));
    }

    protected function zf1(ServerRequestInterface $request)
    {
        return $this->zf2($request);
    }

    protected function zf2(ServerRequestInterface $request)
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

        $nextPage = $page === $totPages ? 0 : $page + 1;
        $prevPage = $page === 1 ? 0 : $page - 1;

        $active = sprintf('/issues/%s', $action);
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
            'repository' => $this->zfComponents,
        ]));
    }
}
