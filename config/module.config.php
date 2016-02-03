<?php
return [
    'lachesis' => [
        'log_dir' => 'data/kharon/lachesis',
        'api_key' => '',
    ],
    'service_manager' => [
        'factories' => [
            \Zend\Db\Adapter\Adapter::class => \Lachesis\Factory::class,
        ],
    ],
];
