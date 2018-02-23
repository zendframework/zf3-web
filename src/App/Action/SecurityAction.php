<?php

namespace App\Action;

use App\Model\Advisory;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Expressive\Template;
use Zend\Feed\Writer\Feed;

class SecurityAction implements RequestHandlerInterface
{
    const ADVISORY_PER_PAGE = 10;
    const ADVISORY_PER_FEED = 15;

    /** @var Advisory */
    private $advisory;

    /** @var Template\TemplateRendererInterface */
    private $template;

    public function __construct(Advisory $advisory, Template\TemplateRendererInterface $template)
    {
        $this->advisory = $advisory;
        $this->template = $template;
    }

    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        $action = $request->getAttribute('action', 'security');

        if ($action === 'feed') {
            return $this->feed($request, $delegate);
        }

        if (! file_exists(sprintf('templates/app/%s.phtml', $action))) {
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
                'next'       => $nextPage,
            ];
        }
        return new HtmlResponse($this->template->render(sprintf('app::%s', $action), $content));
    }

    protected function feed(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $baseUrl = (string) $request->getUri()->withPath('/security');
        $feedUrl = (string) $request->getUri()->withQuery('')->withFragment('');
        $advisoryUrl = $request->getUri()->withPath('/security/advisory');

        $matches = [];
        preg_match('#(?P<type>atom|rss)#', $feedUrl, $matches);
        $feedType = $matches['type'] ?? 'rss';

        $feed = new Feed();
        $feed->setTitle('Zend Framework Security Advisories');
        $feed->setLink($baseUrl);
        $feed->setDescription('Reported and patched vulnerabilities in Zend Framework');
        $feed->setFeedLink($feedUrl, $feedType);

        if ($feedType === 'rss') {
            $feed->addAuthor([
                'name'  => 'Zend Framework Security',
                'email' => 'zf-security@zend.com (Zend Framework Security)',
            ]);
        }

        $advisories = array_slice($this->advisory->getAll(), 0, self::ADVISORY_PER_FEED);
        $first      = current($advisories);
        $feed->setDateModified($first['date']);

        foreach ($advisories as $id => $advisory) {
            $content = $this->advisory->getFromFile($id);
            $entry = $feed->createEntry();
            $entry->setTitle($content['title']);
            $entry->setLink(sprintf('%s/%s', $advisoryUrl, basename($id, '.md')));
            $entry->addAuthor([
                'name'  => 'Zend Framework Security',
                'email' => 'zf-security@zend.com',
            ]);
            $entry->setDateCreated($content['date']);
            $entry->setDateModified($content['date']);
            $entry->setContent($content['body']);
            $feed->addEntry($entry);
        }

        $response = new TextResponse($feed->export($feedType));
        return $response->withHeader('Content-Type', sprintf('application/%s+xml', $feedType));
    }
}
