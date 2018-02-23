<?php

namespace App\Action;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;

class Redirects implements MiddlewareInterface
{
    private $redirects = [
        '/downloads/latest' => '/downloads/archives',
        '/zf2'              => '/',
        '/zf2/'             => '/',
    ];

    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        $uri  = $request->getUri();
        $path = $uri->getPath();

        if (! isset($this->redirects[$path])) {
            return $delegate->handle($request);
        }

        return new RedirectResponse(
            $uri->withPath($this->redirects[$path]),
            StatusCode::STATUS_MOVED_PERMANENTLY
        );
    }
}
