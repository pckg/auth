<?php

namespace Pckg\Auth\Provider;

/*
Registers commands, and middlewared on initialization
*/

use Pckg\Auth\Console\CreateGodfather;
use Pckg\Auth\Controller\Auth as AuthController;
use Pckg\Auth\Controller\Facebook;
use Pckg\Auth\Event\UserLoggedIn;
use Pckg\Auth\Event\UserRegistered;
use Pckg\Auth\Middleware\HandleLoginRequest;
use Pckg\Auth\Middleware\HandleLogoutRequest;
use Pckg\Auth\Middleware\LoginWithAutologinGetParameter;
use Pckg\Auth\Middleware\LoginWithCookie;
use Pckg\Auth\Middleware\RestrictAccess;
use Pckg\Auth\Service\Auth as AuthService;
use Pckg\Framework\Provider;
use Pckg\Framework\Request\Session\SessionUser;

class Auth extends Provider
{

    public function middlewares()
    {
        return [
            LoginWithCookie::class,
            LoginWithAutologinGetParameter::class,
            HandleLogoutRequest::class,
            HandleLoginRequest::class,
            RestrictAccess::class,
        ];
    }

    public function listeners()
    {
        return [
            \Pckg\Auth\Service\Auth::class . '.userLoggedIn' => UserLoggedIn::class,
            'user.registered'                                => UserRegistered::class,
        ];
    }

    public function path()
    {
        return [
            'view' => realpath(__DIR__ . '/../View'),
        ];
    }

    public function view()
    {
        return [
            'object' => [
                '_user' => SessionUser::class,
            ],
        ];
    }

    public function viewObjects()
    {
        return [
            '_auth' => AuthService::class,
        ];
    }

    public function routes()
    {
        return [
            'url' => $this->baseRoutes() + $this->facebookRoutes(),
        ];
    }

    protected function baseRoutes()
    {
        return array_merge_array(
            [
                'controller' => AuthController::class,
            ],
            [
                '/login'                      => [
                    'view' => 'login',
                    'name' => 'login',
                    'tags' => ['auth:out', 'layout:frontend'],
                ],
                '/logout'                     => [
                    'view' => 'logout',
                    'name' => 'pckg.auth.logout',
                ],
                '/register'                   => [
                    'view' => 'register',
                ],
                '/activate-user/[activation]' => [
                    'view' => 'activate',
                ],
                '/forgot-password'            => [
                    'view' => 'forgotPassword',
                    'name' => 'pckg.auth.forgotPassword',
                ],
                '/forgot-password/success'    => [
                    'view' => 'forgotPasswordSuccessful',
                ],
                '/forgot-password/error'      => [
                    'view' => 'forgotPasswordError',
                ],
                '/api/auth/loginStatus'       => [
                    'view' => 'loginStatus',
                    'name' => 'api.auth.loginStatus',
                ],
            ]
        );
    }

    protected function facebookRoutes()
    {
        return array_merge_array(
            [
                'controller' => Facebook::class,
            ],
            [
                '/login/facebook'     => [
                    'view' => 'login',
                    'name' => 'login_facebook',
                ],
                '/takelogin/facebook' => [
                    'view' => 'takelogin',
                    'name' => 'takelogin_facebook',
                ],
            ]
        );
    }

    public function consoles()
    {
        return [
            CreateGodfather::class,
        ];
    }

}