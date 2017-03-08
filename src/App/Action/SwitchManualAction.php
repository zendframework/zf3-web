<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class SwitchManualAction
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $body = json_decode($request->getBody());
        if (! isset($body->old) || ! isset($body->new) || ! isset($body->lang) || ! isset($body->page)) {
            return $response->withStatus(422);
        }
        $newVer = $body->new === 'current' ? $this->config['zf_latest_version'] : $body->new;

        $docFile = $this->config['zf_document_path'][$newVer][$body->lang] . $body->page;
        if (! file_exists($docFile)) {
            if (substr($newVer, 0, 3) === '1.1') {
                $body->page = 'manual.html';
            } elseif (substr($newVer, 0, 2) === '1.' || substr($newVer, 0, 2) === '2.') {
                $body->page = 'index.html';
            }
        }
        return new JsonResponse([
            'url' => sprintf('/manual/%s/%s/%s', $newVer, $body->lang, $body->page),
        ]);
    }
}
