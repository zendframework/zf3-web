<?php

namespace Release;

use Psr\Container\ContainerInterface;

class ReleaseRoutesDelegatorFactory
{
    public function __invoke(ContainerInterface $container, $serviceName, callable $callback)
    {
        $app = $callback();
        $app->post('/releases/new-release', [
            VerifyHubSignatureMiddleware::class,
            AcceptReleaseAction::class,
        ], 'release.new');
        return $app;
    }
}
