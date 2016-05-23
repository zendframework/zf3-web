<?php
chdir(dirname(__DIR__));
require 'vendor/autoload.php';

use App\Model;
use Mni\FrontYAML\Bridge\CommonMark\CommonMarkParser;
use Mni\FrontYAML\Parser;

printf("Building the cache files:\n");

// remove all the .php files in data/cache
$cacheFolder = dirname(__DIR__) . '/data/cache';
foreach (glob("$cacheFolder/*.php") as $filename) {
  unlink($filename);
}

$parser = new Parser(null, new CommonMarkParser());

printf("Advisory...");
$advisory = new Model\Advisory($parser);
printf("done.\n");

printf("Changelog...");
$changelog = new Model\Changelog($parser);
printf("done.\n");

printf("Issue...");
$issue = new Model\Issue($parser);
printf("done.\n");

printf("Post...");
$issue = new Model\Issue($parser);
printf("done.\n");
