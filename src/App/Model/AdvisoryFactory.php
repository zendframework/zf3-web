<?php

namespace App\Model;

use Interop\Container\ContainerInterface;
use Mni\FrontYAML\Bridge\CommonMark\CommonMarkParser;
use Mni\FrontYAML\Parser;

class AdvisoryFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new Advisory(
            new Parser(null, new CommonMarkParser())
        );
    }
}
