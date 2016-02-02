<?php
return [
    'lachesis' => [
        'log_dir' => 'data/kharon/lachesis',
    ],
    'service_manager' => [
        'factories' => [
            \Zend\Db\Adapter\Adapter::class => \Lachesis\Factory::class,
        ],
    ],
];
