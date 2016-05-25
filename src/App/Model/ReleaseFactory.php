<?php
namespace App\Model;

use Interop\Container\ContainerInterface;

class ReleaseFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')['downloads'];

        return new Release(
            $config['release_base_path'],
            $config['versions'],
            $config['manual_languages'],
            $config['products']
        );
    }
}
