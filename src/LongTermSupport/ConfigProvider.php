<?php
declare(strict_types=1);

namespace LongTermSupport;

use Github\Client;
use Http\Client\Common\HttpMethodsClient;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies'      => $this->getDependencies(),
            'long-term-support' => [
                'cache-dir'     => getcwd() . '/data/cache/github/',
                'packages-file' => getcwd() . '/data/long-term-support.json',
                'github-token'  => '',
            ],
        ];
    }

    public function getDependencies() : array
    {
        return [
            'factories' => [
                Client::class                            => Command\GithubClientFactory::class,
                Command\CachedConfigRepository::class    => Command\CachedConfigRepositoryFactory::class,
                Command\GraphqlRepository::class         => Command\GraphqlRepositoryFactory::class,
                Command\PackageList::class               => Command\PackageListFactory::class,
                Command\PackageListBuilderCommand::class => Command\PackageListBuilderCommandFactory::class,
                HttpMethodsClient::class                 => Command\HttpMethodsClientFactory::class,
                LongTermSupportAction::class             => LongTermSupportActionFactory::class,
            ],
        ];
    }
}
