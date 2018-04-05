<?php

namespace Release;

use DateTime;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use League\CommonMark\CommonMarkConverter;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Feed\Reader\Reader;
use Zend\Feed\Writer\Feed;

class AcceptReleaseAction implements RequestHandlerInterface
{
    const DEFAULT_FEED_AUTHOR = [
        'name' => 'Zend Framework Development Team',
        'uri' => 'https://framework.zend.com',
    ];

    /** @var string */
    private $feedPath;

    /** @var CommonMarkConverter */
    private $markdown;

    public function __construct(
        CommonMarkConverter $markdown,
        string $feedPath = 'public/releases'
    ) {
        $this->markdown = $markdown;
        $this->feedPath = rtrim($feedPath, '/');
    }

    /**
     * @return JsonResponse|EmptyResponse
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $content = (string) $request->getBody();
        $data = json_decode($content, true);
        if (! $this->validateData($data)) {
            return new JsonResponse(['error' => 'invalid data provided'], 203);
        }

        $releases = $this->getCurrentReleases();
        $releases->push($this->createRelease($data));
        $feed = $this->createFeed($releases);

        foreach (['rss', 'atom'] as $type) {
            $feed->setFeedLink(sprintf('https://framework.zend.com/releases/%s.xml', $type), $type);
            $this->writeFeedToPath($feed, $type);
        }

        return new EmptyResponse(202);
    }

    private function validateData(array $data) : bool
    {
        return
            isset(
                $data['release'],
                $data['repository']['full_name'],
                $data['release']['tag_name'],
                $data['release']['published_at'],
                $data['release']['author']['login'],
                $data['release']['author']['html_url'],
                $data['release']['html_url']
            )
            && array_key_exists('body', $data['release']);
    }

    private function createRelease(array $data) : Release
    {
        $repo    = $data['repository']['full_name'];
        $version = $data['release']['tag_name'];
        $version = str_replace('release-', '', $version);
        $date    = new DateTime($data['release']['published_at']);

        $author = new Author(
            $data['release']['author']['login'],
            $data['release']['author']['html_url']
        );

        return new Release(
            $repo,
            $version,
            $data['release']['name'] ?? sprintf('%s %s', $repo, $version),
            $data['release']['html_url'],
            $this->markdown->convertToHtml($data['release']['body']),
            $date,
            $author
        );
    }

    private function getCurrentReleases() : Releases
    {
        $xml      = file_get_contents(sprintf('%s/%s.xml', $this->feedPath, 'rss'));
        $feed     = Reader::importString($xml);
        $releases = new Releases();

        foreach ($feed as $entry) {
            $title = $entry->getTitle();
            list($repo, $version) = explode(' ', $title);

            $author = $entry->getAuthor();
            $author = new Author($author['name'], $author['uri'] ?? 'https://framework.zend.com/');

            $date = $entry->getDateCreated();

            $releases->push(new Release(
                $repo,
                $version,
                $title,
                $entry->getLink(),
                $entry->getContent(),
                $date,
                $author
            ));
        }

        return $releases;
    }

    private function createFeed(Releases $releases)
    {
        $feed = new Feed();
        $feed->setTitle('Zend Framework Releases');
        $feed->setLink('https://github.com/zendframework');
        $feed->addAuthor(self::DEFAULT_FEED_AUTHOR);
        $feed->setDescription('Zend Framework and zfcampus releases');

        $latest = false;
        foreach ($releases as $release) {
            $latest = $latest ?: $release->date;

            $entry = $feed->createEntry();
            $entry->setTitle(sprintf('%s %s', $release->repository, $release->version));
            $entry->setLink($release->url);
            $entry->addAuthor($release->author->toArray());
            $entry->setDateCreated($release->date);
            $entry->setDateModified($release->date);
            $entry->setDescription(sprintf('Release information for %s %s', $release->repository, $release->version));
            $entry->setContent($release->content);

            $feed->addEntry($entry);
        }

        $feed->setDateModified($latest);
        $feed->setLastBuildDate($latest);

        return $feed;
    }

    private function writeFeedToPath(Feed $feed, string $type)
    {
        $path = sprintf('%s/%s.xml', $this->feedPath, $type);
        file_put_contents($path, $feed->export($type), LOCK_EX);
    }
}
