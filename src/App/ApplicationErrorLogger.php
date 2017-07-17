<?php

namespace App;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApplicationErrorLogger
{
    const NON_BODY_METHODS = [
        'GET',
        'HEAD',
        'OPTIONS',
        'TRACE',
        'CONNECT',
    ];

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var string
     */
    private $logPath;

    public function __construct(bool $enabled, string $logPath)
    {
        $this->enabled = $enabled;
        $this->logPath = $logPath;
    }

    public function __invoke($error, ServerRequestInterface $request, ResponseInterface $response)
    {
        if (! $this->enabled) {
            return;
        }

        if (empty($this->logPath)) {
            return;
        }

        $method = strtoupper($request->getMethod());
        $message = sprintf(
            "[%s] (%s %s): %s (%s): %s\n",
            date('Y-m-d H:i:s'),
            $method,
            (string) $request->getUri(),
            get_class($error),
            $error->getCode(),
            $error->getMessage()
        );

        if (! in_array($method, self::NON_BODY_METHODS, true)) {
            $message .= sprintf("Request Body:\n%s\n", (string) $request->getBody());
        }

        $log = fopen($this->logPath, 'ab+');
        flock($log, LOCK_EX);
        fwrite($log, $message);
        flock($log, LOCK_UN);
        fclose($log);
    }
}
