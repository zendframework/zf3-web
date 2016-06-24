<?php
$config = [];
if (file_exists(__DIR__ . '/local.php')) {
  $config = include __DIR__ . '/local.php';
}
if (!isset($config['zf_manual_basepath'])) {
  echo "Error: you need to configure the ZF manuals base path 'zf_manual_basepath'";
  exit(1);
}
$paths         = [];
$zf1ManualPath = $config['zf_manual_basepath'] . '/ZendFramework-%s/documentation/manual/core/%s/';
$zf1langs      = ['en', 'de', 'fr', 'ru', 'ja', 'zh'];
$zf1versions   = include __DIR__ . '/zf1-manual-versions.php';

foreach ($zf1versions as $minorVersion => $specificVersion) {
    $paths[$minorVersion] = [];
    foreach ($zf1langs as $lang) {
        $paths[$minorVersion][$lang] = sprintf($zf1ManualPath, $specificVersion, $lang);
    }
}

$zf2ManualPath = $config['zf_manual_basepath'] . '/ZendFramework-%s/manual/%s/';
$zf2versions    = include __DIR__ . '/zf2-manual-versions.php';
$zf2langs       = ['en'];
foreach ($zf2versions as $minorVersion => $specificVersion) {
    $paths[$minorVersion] = [];
    foreach ($zf2langs as $lang) {
        $paths[$minorVersion][$lang] = sprintf($zf2ManualPath, $specificVersion, $lang);
    }
}

krsort($paths);

$zf1MinorVersions = array_keys($zf1versions);
usort($zf1MinorVersions, 'version_compare');

return [
    'manual' => [
        'zf_document_path'             => $paths,
        'zf_apidoc_versions'           => include __DIR__ . '/zf-apidoc-versions.php',
        'zf_latest_version'            => max(array_keys($paths)),
        'zf1_latest_version'           => array_pop($zf1MinorVersions),
        'zf_maintained_major_versions' => [ '1.12' ]
    ]
];
