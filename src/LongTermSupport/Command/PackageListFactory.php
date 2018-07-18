<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

use Http\Client\Common\HttpMethodsClient;
use Psr\Container\ContainerInterface;

class PackageListFactory
{
    public function __invoke(ContainerInterface $container) : PackageList
    {
        return new PackageList(
            $container->get(RepositoryInterface::class),
            $container->get(HttpMethodsClient::class)
        );
    }
}
