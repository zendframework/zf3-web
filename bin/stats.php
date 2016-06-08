<?php
chdir(dirname(__DIR__));
require 'vendor/autoload.php';

$fileZfComponents = 'config/autoload/zf-components.global.php';
if (!file_exists($fileZfComponents)) {
    printf("Error: the %s file doesn't exist!\n", $fileZfComponents);
    exit(1);
}

$zfComponents = require $fileZfComponents;
if (!isset($zfComponents['zf_components'])) {
    printf("Error: the 'zf_components' value doesn't exist!\n");
    exit(1);
}

$fileStats = 'config/autoload/zf-stats.local.php';
$stats = [ 'zf_stats' => [] ];
if (file_exists($fileStats)) {
    $stats = require $fileStats;
}
printf("Generating the stats from packagist.org:\n");
$tot = 0;
foreach ($zfComponents['zf_components'] as $comp) {
    $name   = basename($comp['url']);
    $apiUrl = sprintf("https://packagist.org/packages/zendframework/%s.json", $name);
    if (!isset($stats['zf_stats'][$name])) {
        $stats['zf_stats'][$name] = 0;
    }
    printf("%s...", $name);
    $json = json_decode(file_get_contents($apiUrl));
    $date = time();
    if (!empty($json)) {
        $total = (int) $json->package->downloads->total;
        if ($total > $stats['zf_stats'][$name]) {
            $stats['zf_stats'][$name] = [
                'daily'   => (int) $json->package->downloads->daily,
                'monthly' => (int) $json->package->downloads->monthly,
                'total'   => (int) $json->package->downloads->total
            ];
        }
    }
    printf("done\n");
    $tot += $stats['zf_stats'][$name]['total'];
}

$stats['zf_stats']['total'] = $tot;
$stats['zf_stats']['date']  = $date;

// Write the stats file renaming the file for locking the reading
rename($fileStats, $fileStats . '.lock');
file_put_contents($fileStats . '.lock', '<?php return '. var_export($stats, true) . ';', LOCK_EX);
rename($fileStats . '.lock', $fileStats);
printf("Total installs: %d\n", $tot);
