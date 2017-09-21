<?php
chdir(dirname(__DIR__));
require 'vendor/autoload.php';

use App\Model;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use Mni\FrontYAML\Bridge\CommonMark\CommonMarkParser;
use Mni\FrontYAML\Parser;
use Webuni\CommonMark\TableExtension\TableExtension;

// Assets must be built before we grab configuration, as configuration looks
// for the rev-manifest.json
printf('Building CSS and JS assets...%s', PHP_EOL);
$curdir = getcwd();
chdir(realpath(__DIR__) . '/../asset');
printf('- Installing NPM packages%s', PHP_EOL);
exec('npm install --no-save', $output, $status);
if ($status !== 0) {
    printf(
        '[ERROR] npm install returned a non-zero status:%s%s%s%s',
        PHP_EOL,
        PHP_EOL,
        implode(PHP_EOL, $output),
        PHP_EOL
    );
    exit(1);
}
printf('- Building assets%s', PHP_EOL);
exec('gulp', $output, $status);
if ($status !== 0) {
    printf(
        '[ERROR] gulp returned a non-zero status:%s%s%s%s',
        PHP_EOL,
        PHP_EOL,
        implode(PHP_EOL, $output),
        PHP_EOL
    );
    exit(1);
}
chdir($curdir);
unset($curdir, $output, $status);

printf('Checking for apidoc symlinks... ');

$config = require 'config/config.php';
if (! isset($config['zf_manual_basepath']) || empty($config['zf_manual_basepath'])) {
    printf('Error: the "zf_manual_basepath" config value cannot be empty');
    exit(1);
}
$pathApi = $config['zf_manual_basepath'];
if (substr($pathApi, -1) === '/') {
    $pathApi = substr($pathApi, 0, -1);
}

$ver1 = $config['zf1_manual_versions'];
$ver2 = $config['zf2_manual_versions'];

// symlinks for ZF1
foreach ($ver1 as $file => $ver) {
    if (! file_exists(sprintf('public/apidoc/%s', $file))) {
        $target = sprintf(
            '%s/ZendFramework-%s/documentation/api/core/',
            $pathApi,
            $ver
        );
        symlink($target, sprintf('%s/public/apidoc/%s', dirname(__DIR__), $file));
    }
}

// symlinks for ZF2
foreach ($ver2 as $file => $ver) {
    if (! file_exists(sprintf('public/apidoc/%s', $file))) {
        $target = sprintf(
            '%s/ZendFramework-%s/apidoc/',
            $pathApi,
            $ver
        );
        symlink($target, sprintf('%s/public/apidoc/%s', dirname(__DIR__), $file));
    }
}
printf('done.' . PHP_EOL);

printf('Building the cache files:' . PHP_EOL);

// remove all the .php files in data/cache
$cacheFolder = dirname(__DIR__) . '/data/cache';
foreach (glob(sprintf('%s/*.php', $cacheFolder)) as $filename) {
    if (preg_match('#/issues\.php$#', $filename)) {
        // we really don't need to rebuild issues!
        continue;
    }
    unlink($filename);
}

$cmenv = Environment::createCommonMarkEnvironment();
$cmenv->addExtension(new TableExtension());
$converter = new CommonMarkConverter([], $cmenv);

$parser = new Parser(null, new CommonMarkParser($converter));

printf('Advisory... ');
$advisory = new Model\Advisory($parser);
printf('done.' . PHP_EOL);

printf('Changelog... ');
$changelog = new Model\Changelog($parser);
printf('done.' . PHP_EOL);

printf('Issue... ');
$issue = new Model\Issue($parser);
printf('done.' . PHP_EOL);

printf('Post... ');
$post = new Model\Post($parser);
printf('done.' . PHP_EOL);

printf('Building the config files:' . PHP_EOL);

printf('Update ZF component list... ');
$list = json_decode(
    file_get_contents('https://docs.zendframework.com/zf-mkdoc-theme/scripts/zf-component-list.json'),
    true
);
file_put_contents(
    dirname(__DIR__) . '/config/autoload/zf-components.global.php',
    '<' . '?php return [\'zf_components\' => ' . var_export($list, true) . '];',
    LOCK_EX
);
printf('done.' . PHP_EOL);

printf('Clearing config cache... ');
if (getenv('APP_CACHE')) {
    $configCache = sprintf('%s/app_config.php', getenv('APP_CACHE'));
    if (file_exists($configCache)) {
        unlink($configCache);
    }
}

printf('done.' . PHP_EOL);
