<?php

namespace Release;

use League\CommonMark\CommonMarkConverter;
use Psr\Container\ContainerInterface;
use RuntimeException;

class AcceptReleaseActionFactory
{
    public function __invoke(ContainerInterface $container) : AcceptReleaseAction
    {
        $config = $container->get('config');
        return new AcceptReleaseAction(
            new CommonMarkConverter(),
            $config['release']['feed_path']
        );
    }
}
