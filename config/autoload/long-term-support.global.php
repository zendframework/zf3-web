<?php
declare(strict_types=1);

use LongTermSupport\Command\GraphqlRepository;
use LongTermSupport\Command\RepositoryInterface;

return [
    'dependencies' => [
        'aliases' => [
            RepositoryInterface::class => GraphqlRepository::class,
        ],
    ],
];
