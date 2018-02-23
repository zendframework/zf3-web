<?php

namespace Release;

use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Zend\Diactoros\Response\JsonResponse;

class VerifyHubSignatureMiddleware implements MiddlewareInterface
{
    /** @var ResponseInterface */
    private $responsePrototype;

    /** @var string */
    private $secret;

    /** @var callable */
    private $streamFactory;

    public function __construct(
        string $secret,
        ResponseInterface $responsePrototype,
        callable $streamFactory
    ) {
        $this->secret = $secret;
        $this->responsePrototype = $responsePrototype;
        $this->streamFactory = $streamFactory;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
    {
        $sigSent = $request->getHeaderLine('X-Hub-Signature');
        if (empty($sigSent) || ! preg_match('/^(sha1\=)?(?P<sig>[a-f0-9]+)$/', $sigSent, $matches)) {
            return $this->responsePrototype
                ->withStatus(203)
                ->withBody($this->createJsonResponseBody([
                    'error' => 'missing signature',
                ]));
        }

        $sigSent = $matches['sig'];
        $content = (string) $request->getBody();
        if (hash_hmac('sha1', $content, $this->secret) !== $sigSent) {
            return $this->responsePrototype
                ->withStatus(203)
                ->withBody($this->createJsonResponseBody([
                    'error' => 'signature mismatch',
                ]));
        }

        return $delegate->handle($request);
    }

    private function createJsonResponseBody(array $data) : StreamInterface
    {
        $stream = ($this->streamFactory)();
        $stream->write(json_encode($data));
        return $stream;
    }
}
