<?php
namespace App\Model;

use Interop\Container\ContainerInterface;
use Mni\FrontYAML\Bridge\CommonMark\CommonMarkParser;
use Mni\FrontYAML\Parser;

class IssueFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new Issue(
            new Parser(null, new CommonMarkParser())
        );
    }
}
