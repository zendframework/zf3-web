<?php

namespace Release;

use Psr\Container\ContainerInterface;
use Zend\ProblemDetails\ProblemDetailsMiddleware;

class ReleaseRoutesDelegatorFactory
{
    public function __invoke(ContainerInterface $container, $serviceName, callable $callback)
    {
        $app = $callback();
        $app->post('/releases/new-release', [
            ProblemDetailsMiddleware::class,
            VerifyHubSignatureMiddleware::class,
            AcceptReleaseAction::class,
        ], 'release.new');
        return $app;
    }
}
