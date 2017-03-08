<?php

namespace App\Model;

use Mni\FrontYAML\Bridge\CommonMark\CommonMarkParser;
use Mni\FrontYAML\Parser;
use Psr\Container\ContainerInterface;

class ChangelogFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new Changelog(
            new Parser(null, new CommonMarkParser())
        );
    }
}
