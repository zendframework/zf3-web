<?php
namespace App\Model;

use Interop\Container\ContainerInterface;
use Mni\FrontYAML\Bridge\CommonMark\CommonMarkParser;
use Mni\FrontYAML\Parser;

class ChangelogFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new Changelog(
            new Parser(null, new CommonMarkParser())
        );
    }
}
