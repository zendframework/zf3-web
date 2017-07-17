<?php

namespace Release;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

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

        $response = $container->has(ResponseInterface::class)
            ? $container->get(ResponseInterface::class)
            : new Response();
        $streamFactory = $container->has(StreamInterface::class)
            ? $container->get(StreamInterface::class)
            : function () {
                return new Stream('php://temp', 'wb+');
            };

        return new VerifyHubSignatureMiddleware($secret, $response, $streamFactory);
    }
}
