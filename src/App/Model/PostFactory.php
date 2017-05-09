<?php

namespace App\Model;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use Mni\FrontYAML\Bridge\CommonMark\CommonMarkParser;
use Mni\FrontYAML\Parser;
use Psr\Container\ContainerInterface;
use Webuni\CommonMark\TableExtension\TableExtension;

class PostFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $env = Environment::createCommonMarkEnvironment();
        $env->addExtension(new TableExtension());
        $converter = new CommonMarkConverter([], $env);

        return new Post(
            new Parser(null, new CommonMarkParser($converter))
        );
    }
}
