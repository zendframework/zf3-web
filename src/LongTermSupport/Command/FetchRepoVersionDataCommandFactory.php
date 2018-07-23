<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

use Github\Client;
use Psr\Container\ContainerInterface;

class FetchRepoVersionDataCommandFactory
{
    public function __invoke(ContainerInterface $container) : FetchRepoVersionDataCommand
    {
        return new FetchRepoVersionDataCommand(
            $container->get(Client::class)
        );
    }
}
