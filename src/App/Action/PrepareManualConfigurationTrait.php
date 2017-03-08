<?php

namespace App\Action;

use RuntimeException;

trait PrepareManualConfigurationTrait
{
    private function prepareManualConfiguration($config)
    {
        $this->validateRequiredKeys($config);

        $paths         = [];
        $zf1ManualPath = $config['zf_manual_basepath'] . '/ZendFramework-%s/documentation/manual/core/%s/';
        $zf1langs      = ['en', 'de', 'fr', 'ru', 'ja', 'zh'];
        $zf1versions   = $config['zf1_manual_versions'];

        foreach ($zf1versions as $minorVersion => $specificVersion) {
            $paths[$minorVersion] = [];
            foreach ($zf1langs as $lang) {
                $paths[$minorVersion][$lang] = sprintf($zf1ManualPath, $specificVersion, $lang);
            }
        }

        $zf2ManualPath = $config['zf_manual_basepath'] . '/ZendFramework-%s/manual/%s/';
        $zf2versions   = $config['zf2_manual_versions'];
        $zf2langs      = ['en'];
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
            'zf_document_path'             => $paths,
            'zf_apidoc_versions'           => $config['zf_apidoc_versions'],
            'zf_latest_version'            => max(array_keys($paths)),
            'zf1_latest_version'           => array_pop($zf1MinorVersions),
            'zf_maintained_major_versions' => ['1.12'],
        ];
    }

    private function validateRequiredKeys($config)
    {
        if (! isset($config['zf_manual_basepath'])) {
            throw new RuntimeException(
                'Error: you need to configure the ZF manuals base path "zf_manual_basepath"'
            );
        }

        if (! isset($config['zf1_manual_versions'])) {
            throw new RuntimeException(
                'Error: you need to configure the ZF1 manual versions list "zf1_manual_versions"'
            );
        }

        if (! isset($config['zf2_manual_versions'])) {
            throw new RuntimeException(
                'Error: you need to configure the ZF2 manual versions list "zf2_manual_versions"'
            );
        }

        if (! isset($config['zf_apidoc_versions'])) {
            throw new RuntimeException(
                'Error: you need to configure the ZF ApiDoc versions list "zf_apidoc_versions"'
            );
        }
    }
}
