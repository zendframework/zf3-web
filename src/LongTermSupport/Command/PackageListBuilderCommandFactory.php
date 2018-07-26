<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

use Psr\Container\ContainerInterface;

class PackageListBuilderCommandFactory
{
    public function __invoke(ContainerInterface $container) : PackageListBuilderCommand
    {
        $packageFile = $container->get('config')['long-term-support']['packages-file'] ?? '';
        return new PackageListBuilderCommand(
            $container->get(PackageList::class),
            $packageFile
        );
    }
}
