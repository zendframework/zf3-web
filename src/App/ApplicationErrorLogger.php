<?php

namespace App;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

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
            "[%s] (%s %s): %s",
            date('Y-m-d H:i:s'),
            $method,
            (string) $request->getUri(),
            $this->getErrorMessage($error)
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

    private function getErrorMessage(Throwable $e) : string
    {
        $message = sprintf(
            "%s (%s) in %s line %d: %s\n%s\n",
            get_class($e),
            $e->getCode(),
            $e->getFile(),
            $e->getLine(),
            $e->getMessage(),
            $e->getTraceAsString()
        );
        $previous = $e->getPrevious();
        return $previous ? $message . $this->getErrorMessage($e) : $message;
    }
}
