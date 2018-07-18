<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

use Github\Client;
use Http\Client\Common\HttpMethodsClient;
use Psr\Container\ContainerInterface;

class HttpMethodsClientFactory
{
    public function __invoke(ContainerInterface $container) : HttpMethodsClient
    {
        $client = $container->get(Client::class);
        return $client->getHttpClient();
    }
}
