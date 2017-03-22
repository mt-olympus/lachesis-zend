<?php
return [
    'lachesis' => [
        'enabled' => true,
        'log_dir' => 'data/kharon/lachesis',
        'log_file' => null,
        'api_key' => '',
    ],
    'dependencies' => [
        'factories' => [
            Lachesis\Lachesis::class => Lachesis\Factory::class,
            Lachesis\LachesisMiddleware::class => Lachesis\LachesisMiddlewareFactory::class,
        ],
    ],
    'middleware_pipeline' => [
        'lachesis' => [
            'middleware' => [
                Lachesis\LachesisMiddleware::class,
            ],
            'priority' => 9999,
        ],
    ],
];
