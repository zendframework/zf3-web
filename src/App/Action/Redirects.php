<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;

class Redirects
{
    private $redirects = [
        '/downloads/latest' => '/downloads/archives',
        '/zf2'              => '/',
        '/zf2/'             => '/',
    ];

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $uri  = $request->getUri();
        $path = $uri->getPath();

        if (! isset($this->redirects[$path])) {
            return $next($request, $response);
        }

        return new RedirectResponse(
            $uri->withPath($this->redirects[$path]),
            301
        );
    }
}
