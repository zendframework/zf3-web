<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

use Psr\Container\ContainerInterface;

class PackageListBuilderCommandFactory
{
    public function __invoke(ContainerInterface $container) : PackageListBuilderCommand
    {
        return new PackageListBuilderCommand(
            $container->get(PackageList::class)
        );
    }
}
