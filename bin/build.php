<?php
chdir(dirname(__DIR__));
require 'vendor/autoload.php';

use App\Model;
use Mni\FrontYAML\Bridge\CommonMark\CommonMarkParser;
use Mni\FrontYAML\Parser;

printf("Checking for apidoc symlinks...");

if (!file_exists('config/autoload/local.php')) {
    printf("Error: you need to create a config/autoload/local.php file");
    exit(1);
}
$config = require "config/autoload/local.php";
if (!isset($config['zf_manual_basepath']) || empty($config['zf_manual_basepath'])) {
    printf("Error: the 'zf_manual_basepath' config value cannot be empty");
    exit(1);
}
$pathApi = $config['zf_manual_basepath'];
if (substr($pathApi, -1) == '/') {
    $pathApi = substr($pathApi, 0, strlen($pathApi) - 1);
}

$ver1 = [
    '1.0' => '1.0.3',
    '1.5' => '1.5.3',
    '1.6' => '1.6.2',
    '1.7' => '1.7.9',
    '1.8' => '1.8.5',
    '1.9' => '1.9.8',
    '1.10' => '1.10.9',
    '1.11' => '1.11.15',
    '1.12' => '1.12.18'
];
$ver2 = [
    '2.0' => '2.0.7',
    '2.1' => '2.1.5',
    '2.2' => '2.2.10',
    '2.3' => '2.3.9',
    '2.4' => '2.4.9'
];

// symlinks for ZF1
foreach ($ver1 as $file => $ver) {
    if (! file_exists("public/apidoc/$file")) {
        $target = sprintf(
            "%s/ZendFramework-%s/documentation/api/core/",
            $pathApi,
            $ver
        );
        symlink($target, dirname(__DIR__) . "/public/apidoc/$file");
    }
}

// symlinks for ZF2
foreach ($ver2 as $file => $ver) {
    if (! file_exists("public/apidoc/$file")) {
        $target = sprintf(
            "%s/ZendFramework-%s/apidoc/",
            $pathApi,
            $ver
        );
        symlink($target, dirname(__DIR__) . "/public/apidoc/$file");
    }
}
printf("done.\n");

printf("Building the cache files:\n");

// remove all the .php files in data/cache
$cacheFolder = dirname(__DIR__) . '/data/cache';
foreach (glob("$cacheFolder/*.php") as $filename) {
    if (preg_match('#/issues\.php$#', $filename)) {
        // we really don't need to rebuild issues!
        continue;
    }
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
$post = new Model\Post($parser);
printf("done.\n");

printf("Building the config files:\n");

printf("Update ZF component list...");
$list = json_decode(
    file_get_contents('http://zendframework.github.io/zf-mkdoc-theme/scripts/zf-component-list.json'),
    true
);
file_put_contents(
    dirname(__DIR__) . '/config/autoload/zf-components.global.php',
    '<' . '?php return [ "zf_components" => ' . var_export($list, true) . ' ];',
    LOCK_EX
);
printf("done.\n");

printf("Clearing config cache...");
if (getenv('APP_CACHE')) {
    $configCache = sprintf('%s/app_config.php', getenv('APP_CACHE'));
    if (file_exists($configCache)) {
        unlink($configCache);
    }
}

printf("done.\n");
