<?php

namespace Release;

use Zend\Expressive\Application;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => [
                'delegators' => [
                    Application::class => [
                        ReleaseRoutesDelegatorFactory::class,
                    ],
                ],
                'factories' => [
                    AcceptReleaseAction::class => AcceptReleaseActionFactory::class,
                    ErrorHandlerMiddleware::class => ErrorHandlerMiddlewareFactory::class,
                    VerifyHubSignatureMiddleware::class => VerifyHubSignatureMiddlewareFactory::class,
                ],
            ],
            'api' => [
                'debug' => false,
            ],
            'hub' => [
                'secret' => false,
            ],
            'release' => [
                'feed_path' => 'public/releases',
            ],
        ];
    }
}
