<?php

namespace App\Model;

use Mni\FrontYAML\Bridge\CommonMark\CommonMarkParser;
use Mni\FrontYAML\Parser;
use Psr\Container\ContainerInterface;

class IssueFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new Issue(
            new Parser(null, new CommonMarkParser())
        );
    }
}
