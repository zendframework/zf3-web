<?php
chdir(dirname(__DIR__));
require 'vendor/autoload.php';

use Zend\Config\Writer;

if (! isset($argv[1])) {
    fwrite(STDERR, printf("Usage: php %s <path/to/stat>\n", basename(__FILE__)));
    exit(1);
}

$fileStats = $argv[1];

$fileZfComponents = 'config/autoload/zf-components.local.php';
if (! file_exists($fileZfComponents)) {
    fwrite(STDERR, sprintf("Error: the file '%s' does not exist!\n", $fileZfComponents));
    exit(1);
}

$zfComponents = require $fileZfComponents;
if (! isset($zfComponents['zf_components'])) {
    fwrite(STDERR, "Error: the 'zf_components' value doesn't exist!\n");
    exit(1);
}

$stats = ['zf_stats' => []];

printf("Generating the stats from packagist.org:\n");
$tot = 0;
foreach ($zfComponents['zf_components'] as $name => $comp) {
    $apiUrl = sprintf('https://packagist.org/packages/zendframework/%s.json', $name);

    $content = @file_get_contents($apiUrl);
    if ($content === false) {
        continue;
    }

    printf('%s... ', $name);
    $json = json_decode($content);
    if (! empty($json->package->downloads)) {
        $stats['zf_stats'][$name] = [
            'daily'   => (int) $json->package->downloads->daily,
            'monthly' => (int) $json->package->downloads->monthly,
            'total'   => (int) $json->package->downloads->total,
        ];

        $tot += $stats['zf_stats'][$name]['total'];
    }

    printf("done\n");
}

$stats['zf_stats']['total'] = $tot;
$stats['zf_stats']['date']  = time();

// Store the stats and create the link under config/autoload
file_put_contents($fileStats, '<' . '?php return '. var_export($stats, true) . ';', LOCK_EX);

$localConfig = 'config/autoload/zf-stats.local.php';
if (file_exists($localConfig)) {
    unlink($localConfig);
}

if (! symlink($fileStats, $localConfig)) {
    fwrite(STDERR, printf("Error: Cannot symlink to '%s'\n", $fileStats));
    exit(1);
}

printf("Total installs: %d\n", $tot);
