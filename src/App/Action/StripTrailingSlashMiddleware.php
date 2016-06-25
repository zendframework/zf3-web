<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Strip trailing slashes from paths and permanently redirect.
 */
class StripTrailingSlashMiddleware
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $uri = $request->getUri();
        $path = $uri->getPath();

        if ('/' !== $path && preg_match('#/$#', $path)) {
            return new RedirectResponse(
                (string) $uri->withPath(rtrim($path, '/')),
                301
            );
        }

        return $next($request, $response);
    }
}
