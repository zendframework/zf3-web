<?php
return [
    'zf_manual_basepath' => realpath(getcwd()) . '/data/manual/',

    'router' => [
        'routes' => [
            'manual' => [
                'options' => [
                    'defaults' => [
                        'version' => '2.4',
                    ],
                ],
            ],
        ],
    ],
];
