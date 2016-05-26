<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use App\Model\Advisory;
use Zend\Feed\Writer\Feed;

class SecurityAction
{
    const ADVISORY_PER_PAGE = 10;
    const ADVISORY_PER_FEED = 15;

    private $advisory;

    public function __construct(Advisory $advisory, Template\TemplateRendererInterface $template = null)
    {
        $this->advisory = $advisory;
        $this->template = $template;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $action = $request->getAttribute('action', 'security');

        if ($action === 'feed') {
            return $this->feed($request, $response, $next);
        }
        if (! file_exists("templates/app/$action.phtml")) {
            return new HtmlResponse($this->template->render('error::404'));
        }
        $content = [];
        if ('advisories' === $action) {
            $params = $request->getQueryParams();
            $page = isset($params['page']) ? (int) $params['page'] : 1;

            $allAdvisories = $this->advisory->getAll();
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

    protected function feed(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $feed = new Feed();
        $feed->setTitle('Zend Framework Security Advisories');
        $feed->setLink('http://framework.zend.com/security');
        $feed->setDescription('Reported and patched vulnerabilities in Zend Framework');
        $feed->setFeedLink('http://framework.zend.com/security/feed', 'atom');
        $feed->setLanguage('en-us');
        /*
        $feed->addAuthor([
            'email' => 'zf-security@zend.com (Zend Framework Security)'
        ]);
        */
        $advisories = array_slice($this->advisory->getAll(), 0, self::ADVISORY_PER_FEED);
        $first      = current($advisories);
        $feed->setDateModified($first['date']);

        foreach($advisories as $id => $advisory) {
            $content = $this->advisory->getFromFile($id);
            $entry = $feed->createEntry();
            $entry->setTitle($content['title']);
            $entry->setLink('http://framework.zend.com//security/advisory/' . basename($id, '.md'));
            $entry->addAuthor([
                'name' => 'Zend Framework Security',
                'email' => 'zf-security@zend.com'
            ]);
            $entry->setDateCreated($content['date']);
            $entry->setDateModified($content['date']);
            $entry->setContent($content['body']);
            $feed->addEntry($entry);
        }

        $response->getBody()->write($feed->export('atom'));
        return $response;

    }
}
