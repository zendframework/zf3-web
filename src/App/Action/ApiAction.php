<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;

class ApiAction implements MiddlewareInterface
{
    /** @var array */
    private $versions;

    public function __construct(array $versions)
    {
        $this->versions = $versions;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $version = $request->getQueryParams()['v'] ?? '1';
        if (! isset($this->versions[$version])) {
            $version = '1';
        }

        return new TextResponse($this->versions[$version]);
    }
}
