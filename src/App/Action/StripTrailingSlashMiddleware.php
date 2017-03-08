<?php

namespace App\Action;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Strip trailing slashes from paths and permanently redirect.
 */
class StripTrailingSlashMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $uri = $request->getUri();
        $path = $uri->getPath();

        if ('/' !== $path && preg_match('#/$#', $path)) {
            return new RedirectResponse(
                (string) $uri->withPath(rtrim($path, '/')),
                StatusCode::STATUS_MOVED_PERMANENTLY
            );
        }

        return $delegate->process($request);
    }
}
