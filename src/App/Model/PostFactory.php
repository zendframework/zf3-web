<?php

namespace App\Model;

use Mni\FrontYAML\Bridge\CommonMark\CommonMarkParser;
use Mni\FrontYAML\Parser;
use Psr\Container\ContainerInterface;

class PostFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new Post(
            new Parser(null, new CommonMarkParser())
        );
    }
}
