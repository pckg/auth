<?php

return [
    'providers' => [
        'url' => [
            '/login-status'               => [
                'controller' => Pckg\Auth\Controller\Auth::class,
                'view'       => 'loginStatus',
                'name'       => 'loginStatus',
            ],
            '/login'                      => [
                'controller' => Pckg\Auth\Controller\Auth::class,
                'view'       => 'login',
                'name'       => 'login',
            ],
            '/login/facebook'             => [
                'controller' => Pckg\Auth\Controller\Facebook::class,
                'view'       => 'login',
                'name'       => 'login_facebook',
            ],
            '/takelogin/facebook'         => [
                'controller' => Pckg\Auth\Controller\Facebook::class,
                'view'       => 'takelogin',
                'name'       => 'takelogin_facebook',
            ],
            '/logout'                     => [
                'controller' => Pckg\Auth\Controller\Auth::class,
                'view'       => 'logout',
            ],
            '/register'                   => [
                'controller' => Pckg\Auth\Controller\Auth::class,
                'view'       => 'register',
            ],
            '/activate-user/[activation]' => [
                'controller' => Pckg\Auth\Controller\Auth::class,
                'view'       => 'activate',
            ],
            '/forgot-password'            => [
                'controller' => Pckg\Auth\Controller\Auth::class,
                'view'       => 'forgotPassword',
            ],
            '/forgot-password/success'    => [
                'controller' => Pckg\Auth\Controller\Auth::class,
                'view'       => 'forgotPasswordSuccesful',
            ],
            '/forgot-password/error'      => [
                'controller' => Pckg\Auth\Controller\Auth::class,
                'view'       => 'forgotPasswordError',
            ],
        ],
    ],
];