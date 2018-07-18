#!/usr/bin/env php
<?php
declare(strict_types=1);

use LongTermSupport\Command\PackageListBuilderCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

require __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/../config/container.php';

$loader = new FactoryCommandLoader([
    'lts:build' => function () use ($container) {
        return $container->get(PackageListBuilderCommand::class);
    },
]);

$application = new Application();
$application->setCommandLoader($loader);
$application->run();
