<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

use Psr\Container\ContainerInterface;

class CachedConfigRepositoryFactory
{
    public function __invoke(ContainerInterface $container) : CachedConfigRepository
    {
        $config = $container->get('config')['long-term-support'];
        return new CachedConfigRepository(
            include($config['cache-dir'] . '/results.php'),
            (new RepositoryFiltersFactory())()
        );
    }
}
