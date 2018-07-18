<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

use Cache\Adapter\Filesystem\FilesystemCachePool;
use Github\Client;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Psr\Container\ContainerInterface;

class GithubClientFactory
{
    public function __invoke(ContainerInterface $container) : Client
    {
        $config = $container->get('config')['long-term-support'];
        $cachePool = new FilesystemCachePool(new Filesystem(new Local($config['cache-dir'])));
        $client = new Client();
        $client->addCache($cachePool);
        $client->authenticate($config['github-token'], Client::AUTH_HTTP_TOKEN);
        return $client;
    }
}
