<?php

namespace App;

use Psr\Http\Message\ResponseInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HostnameMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        return $response->withHeader(
            'X-Backend-Server',
            $this->getHostname()
        );
    }

    private function getHostname() : string
    {
        $hostname = gethostname();
        if (false !== $hostname) {
            return $hostname;
        }

        $hostname = file_get_contents('/etc/hostname');
        return trim($hostname);
    }
}
