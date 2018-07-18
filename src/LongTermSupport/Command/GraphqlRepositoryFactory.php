<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

use Psr\Container\ContainerInterface;
use Github\Client;

class GraphqlRepositoryFactory
{
    public function __invoke(ContainerInterface $container) : GraphqlRepository
    {
        return new GraphqlRepository(
            $container->get(Client::class),
            (new RepositoryFiltersFactory())()
        );
    }
}
