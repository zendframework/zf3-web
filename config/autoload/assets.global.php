<?php
return [
    'view_helper_config' => [
        'asset' => [
            'resource_map' => json_decode(file_get_contents(__DIR__ . '/../../asset/rev-manifest.json'), true),
        ],
    ],
];
