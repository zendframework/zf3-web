<?php

namespace App\Action;

use App\Model\Post;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Expressive\Template;
use Zend\Feed\Writer\Feed;

class BlogAction implements MiddlewareInterface
{
    const POST_PER_PAGE = 10;
    const POST_PER_FEED = 15;

    /** @var Post */
    private $posts;

    /** @var Template\TemplateRendererInterface */
    private $template;

    public function __construct(Post $posts, Template\TemplateRendererInterface $template)
    {
        $this->posts    = $posts;
        $this->template = $template;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (preg_match('#/feed.*?\.xml$#', $request->getUri()->getPath())) {
            return $this->feed($request, $delegate);
        }

        $file = $request->getAttribute('file', false);

        if (! $file) {
            return $this->blogPage($request, $delegate);
        }

        $post = sprintf('data/posts/%s.md', basename($file, '.html'));
        if (! file_exists($post)) {
            return new HtmlResponse($this->template->render('error::404'));
        }

        $content = $this->posts->getFromFile($post);
        $content['posts'] = array_slice($this->posts->getAll(), 0, self::POST_PER_PAGE);
        $content['layout'] = 'layout::default';

        return new HtmlResponse($this->template->render('app::post', $content));
    }

    protected function blogPage(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $params = $request->getQueryParams();
        $page = isset($params['page']) ? (int) $params['page'] : 1;

        $allPosts = $this->posts->getAll();
        $totPages = ceil(count($allPosts) / self::POST_PER_PAGE);

        if ($page > $totPages || $page < 1) {
            return new HtmlResponse($this->template->render('error::404'));
        }
        $nextPage = $page === $totPages ? 0 : $page + 1;
        $prevPage = $page === 1 ? 0 : $page - 1;

        $posts = array_slice($allPosts, ($page - 1) * self::POST_PER_PAGE, self::POST_PER_PAGE);
        foreach ($posts as $key => $value) {
            $posts[$key]['excerpt'] = substr(strip_tags($this->posts->getFromFile($key)['body']), 0, 256);
        }
        return new HtmlResponse($this->template->render('app::blog', [
            'posts' => $posts,
            'tot'   => $totPages,
            'page'  => $page,
            'prev'  => $prevPage,
            'next'  => $nextPage,
        ]));
    }

    protected function feed(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $uri     = $request->getUri();
        $blogUrl = (string) $uri->withPath('/blog');
        $feedUrl = (string) $uri->withQuery('')->withFragment('');
        $baseUrl = sprintf(
            '%s://%s',
            $uri->getScheme(),
            $uri->getPort() === 80 ? $uri->getHost() : $uri->getHost() . ':' . $uri->getPort()
        );

        $matches = [];
        preg_match('#(?P<type>atom|rss)#', $feedUrl, $matches);
        $feedType = $matches['type'] ?? 'rss';

        $feed = new Feed();
        $feed->setTitle('Blog Entries - ZF Blog');
        $feed->setLink($blogUrl);
        $feed->setDescription('Blog Entries - ZF Blog');
        $feed->setFeedLink($feedUrl, $feedType);

        $dateModified = false;

        foreach (array_slice($this->posts->getAll(), 0, self::POST_PER_FEED) as $id => $post) {
            $content = $this->posts->getFromFile($id);

            $dateModified = $dateModified ?: $content['date'];

            $entry = $feed->createEntry();
            $entry->setTitle($content['title']);
            $entry->setLink($baseUrl . $content['permalink']);
            $entry->addAuthor([
                'name' => $content['author'],
                'url'  => $content['url_author'],
            ]);
            $entry->setDateCreated($content['date']);
            $entry->setDateModified($content['date']);
            $entry->setContent($content['body']);
            $feed->addEntry($entry);
        }

        $feed->setDateModified($dateModified);

        $response = new TextResponse($feed->export($feedType));
        return $response->withHeader('Content-Type', sprintf('application/%s+xml', $feedType));
    }
}
