<?php

namespace Release;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class VerifyHubSignatureMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : VerifyHubSignatureMiddleware
    {
        $config = $container->get('config');
        $secret = $config['hub']['secret'] ?? false;
        if (! $secret) {
            throw new RuntimeException(sprintf(
                'Cannot create %s; missing "hub.secret" configuration',
                VerifyHubSignatureMiddleware::class
            ));
        }

        return new VerifyHubSignatureMiddleware($secret, $container->get(ResponseInterface::class));
    }
}
