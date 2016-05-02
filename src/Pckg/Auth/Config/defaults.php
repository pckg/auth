<?php

return [
    'auth' => [
        'storage'  => 'session',
        'class'    => Pckg\Framework\Request\Session\SessionUser::class,
        'provider' => [
            'database' => [],
            'facebook' => [
                'app_id'                => '457935907718466',
                'app_secret'            => '0b12d1a051513e7895708658c0dfeb1d',
                'default_graph_version' => 'v2.2',
            ],
            'twitter'  => [],
            'google'   => [],
        ],
    ],
];
