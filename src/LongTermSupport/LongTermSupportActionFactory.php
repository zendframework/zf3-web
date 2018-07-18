<?php
declare(strict_types=1);

namespace LongTermSupport;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class LongTermSupportActionFactory
{
    public function __invoke(ContainerInterface $container) : LongTermSupportAction
    {
        return new LongTermSupportAction(
            $container->get(TemplateRendererInterface::class),
            $container->get('config')['long-term-support']['packages-file']
        );
    }
}
