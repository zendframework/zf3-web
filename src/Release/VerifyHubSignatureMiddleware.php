<?php

namespace Release;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface;

class VerifyHubSignatureMiddleware implements MiddlewareInterface
{
    /** @var callable */
    private $responseFactory;

    /** @var string */
    private $secret;

    public function __construct(
        string $secret,
        callable $responseFactory
    ) {
        $this->secret = $secret;
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $handler) : ResponseInterface
    {
        $sigSent = $request->getHeaderLine('X-Hub-Signature');
        if (empty($sigSent) || ! preg_match('/^(sha1\=)?(?P<sig>[a-f0-9]+)$/', $sigSent, $matches)) {
            $response = ($this->responseFactory)();
            $response->write(json_encode([
                'error' => 'missing signature',
            ]));
            return $response->withStatus(203);
        }

        $sigSent = $matches['sig'];
        $content = (string) $request->getBody();
        if (hash_hmac('sha1', $content, $this->secret) !== $sigSent) {
            $response = ($this->responseFactory)();
            $response->write(json_encode([
                'error' => 'signature mismatch',
            ]));
            return $response->withStatus(203);
        }

        return $handler->handle($request);
    }
}
