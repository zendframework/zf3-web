<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;

class ApiAction implements RequestHandlerInterface
{
    /** @var array */
    private $versions;

    public function __construct(array $versions)
    {
        $this->versions = $versions;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $version = $request->getQueryParams()['v'] ?? '1';
        if (! isset($this->versions[$version])) {
            $version = '1';
        }

        return new TextResponse($this->versions[$version]);
    }
}
