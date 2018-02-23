<?php

namespace App\Action;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class SwitchManualAction implements RequestHandlerInterface
{
    /** @var array */
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        $body = json_decode($request->getBody());
        if (! isset($body->old) || ! isset($body->new) || ! isset($body->lang) || ! isset($body->page)) {
            return new EmptyResponse(StatusCode::STATUS_UNPROCESSABLE_ENTITY);
        }
        $newVer = $body->new === 'current' ? $this->config['zf_latest_version'] : $body->new;

        $docFile = $this->config['zf_document_path'][$newVer][$body->lang] . $body->page;
        if (! file_exists($docFile)) {
            if (strpos($newVer, '1.1') === 0) {
                $body->page = 'manual.html';
            } elseif (strpos($newVer, '1.') === 0 || strpos($newVer, '2.') === 0) {
                $body->page = 'index.html';
            }
        }
        return new JsonResponse([
            'url' => sprintf('/manual/%s/%s/%s', $newVer, $body->lang, $body->page),
        ]);
    }
}
