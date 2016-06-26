<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Expressive\Template;
use App\Model\Post;
use Zend\Feed\Writer\Feed;

class BlogAction
{
    const POST_PER_PAGE = 10;
    const POST_PER_FEED = 15;

    private $template;

    private $posts;

    public function __construct(Post $posts, Template\TemplateRendererInterface $template = null)
    {
        $this->posts    = $posts;
        $this->template = $template;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        if (preg_match('#/feed.*?\.xml$#', $request->getUri()->getPath())) {
            return $this->feed($request, $response, $next);
        }

        $file = $request->getAttribute('file', false);

        if (! $file) {
            return $this->blogPage($request, $response, $next);
        }

        $post = 'data/posts/' . basename($file, '.html') . '.md';
        if (! file_exists($post)) {
            return new HtmlResponse($this->template->render('error::404'));
        }

        $content = $this->posts->getFromFile($post);
        $content['posts'] = array_slice($this->posts->getAll(), 0, self::POST_PER_PAGE);
        $content['layout'] = 'layout::default';

        return new HtmlResponse($this->template->render('app::post', $content));
    }

    protected function blogPage(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $params = $request->getQueryParams();
        $page = isset($params['page']) ? (int) $params['page'] : 1;

        $allPosts = $this->posts->getAll();
        $totPages = ceil(count($allPosts) / self::POST_PER_PAGE);

        if ($page > $totPages || $page < 1) {
            return new HtmlResponse($this->template->render('error::404'));
        }
        $nextPage = ($page === $totPages) ? 0 : $page + 1;
        $prevPage = ($page === 1) ? 0 : $page - 1;

        $posts = array_slice($allPosts, ($page - 1) * self::POST_PER_PAGE, self::POST_PER_PAGE);
        foreach ($posts as $key => $value) {
            $posts[$key]['excerpt'] = substr(strip_tags($this->posts->getFromFile($key)['body']), 0, 256);
        }
        return new HtmlResponse($this->template->render('app::blog', [
            'posts' => $posts,
            'tot'   => $totPages,
            'page'  => $page,
            'prev'  => $prevPage,
            'next'  => $nextPage
        ]));
    }

    protected function feed(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $blogUrl = (string) $request->getUri()->withPath('/blog');
        $feedUrl = (string) $request->getUri()->withQuery('')->withFragment('');

        $matches = [];
        preg_match('#(?P<type>atom|rss)#', $feedUrl, $matches);
        $feedType = isset($matches['type']) ? $matches['type'] : 'rss';

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
            $entry->setLink($content['permalink']);
            $entry->addAuthor([
                'name' => $content['author'],
                'url'  => $content['url_author']
            ]);
            $entry->setDateCreated($content['date']);
            $entry->setDateModified($content['date']);
            $entry->setContent($content['body']);
            $feed->addEntry($entry);
        }

        $feed->setDateModified($dateModified);

        $response = new TextResponse($feed->export($feedType));
        return $response->withHeader('Content-Type', 'application/' . $feedType . '+xml');
    }
}
