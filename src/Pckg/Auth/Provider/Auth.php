<?php

namespace Pckg\Auth\Provider;

/*
Registers commands, and middlewared on initialization
*/

use Pckg\Auth\Command;
use Pckg\Auth\Controller\Auth as AuthController;
use Pckg\Auth\Controller\Facebook;
use Pckg\Auth\Event;
use Pckg\Auth\Event\UserLoggedIn;
use Pckg\Auth\Event\UserRegistered;
use Pckg\Auth\Middleware;
use Pckg\Auth\Middleware\HandleLoginRequest;
use Pckg\Auth\Middleware\HandleLogoutRequest;
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
            'auth.loginWithCookie'     => LoginWithCookie::class,
            'auth.handleLogoutRequest' => HandleLogoutRequest::class,
            'auth.handleLoginRequest'  => HandleLoginRequest::class,
            'auth.restrictAccess'      => RestrictAccess::class,
        ];
    }

    public function event()
    {
        return [
            'user.loggedIn'   => UserLoggedIn::class,
            'user.registered' => UserRegistered::class,
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
                '/login-status'               => [
                    'view' => 'loginStatus',
                    'name' => 'loginStatus',
                ],
                '/login'                      => [
                    'view' => 'login',
                    'name' => 'login',
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

}