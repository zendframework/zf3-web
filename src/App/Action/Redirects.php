<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;

class Redirects implements MiddlewareInterface
{
    private $redirects = [
        '/downloads/latest' => '/downloads/archives',
        '/zf2'              => '/',
        '/zf2/'             => '/',
    ];

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $uri  = $request->getUri();
        $path = $uri->getPath();

        if (! isset($this->redirects[$path])) {
            return $delegate->process($request);
        }

        return new RedirectResponse(
            $uri->withPath($this->redirects[$path]),
            301
        );
    }
}
