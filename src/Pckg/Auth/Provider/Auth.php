<?php

namespace Pckg\Auth\Provider;

/*
Registers commands, and middlewared on initialization
*/

use Pckg\Auth\Console\CreateGodfather;
use Pckg\Auth\Console\CreateUserGroups;
use Pckg\Auth\Controller\Auth as AuthController;
use Pckg\Auth\Controller\Facebook;
use Pckg\Auth\Event\UserLoggedIn;
use Pckg\Auth\Event\UserRegistered;
use Pckg\Auth\Middleware\HandleLoginRequest;
use Pckg\Auth\Middleware\HandleLogoutRequest;
use Pckg\Auth\Middleware\LoginWithApiKeyHeader;
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
            LoginWithApiKeyHeader::class,
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

    public function viewObjects()
    {
        return [
            '_auth' => AuthService::class,
            '_user' => SessionUser::class,
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
        return array_merge_array([
                                     'controller' => AuthController::class,
                                 ], [
                                     '/login'                      => [
                                         'view' => 'login',
                                         'name' => 'login',
                                         'tags' => ['auth:out', 'layout:frontend', 'seo:title' => 'Login'],
                                     ],
                                     '/logout'                     => [
                                         'view' => 'logout',
                                         'name' => 'pckg.auth.logout',
                                     ],
                                     '/api/auth/signup'            => [
                                         'view' => 'signup',
                                     ],
                                     '/activate-user/[activation]' => [
                                         'view' => 'activate',
                                         'name' => 'pckg.auth.activate',
                                     ],
                                     '/forgot-password'            => [
                                         'view' => 'forgotPassword',
                                         'name' => 'pckg.auth.forgotPassword',
                                     ],
                                     '/password-code'              => [
                                         'view' => 'passwordCode',
                                         'name' => 'pckg.auth.passwordCode',
                                     ],
                                     '/reset-password'             => [
                                         'view' => 'resetPassword',
                                         'name' => 'pckg.auth.resetPassword',
                                     ],
                                     '/api/auth/user'              => [
                                         'view' => 'user',
                                         'name' => 'api.auth.user',
                                     ],
                                     '/api/auth/user/addresses'    => [
                                         'view' => 'userAddresses',
                                         'name' => 'api.auth.user.addresses',
                                     ],
                                 ]);
    }

    protected function facebookRoutes()
    {
        return array_merge_array([
                                     'controller' => Facebook::class,
                                 ], [
                                     '/login/facebook'     => [
                                         'view' => 'login',
                                         'name' => 'login_facebook',
                                     ],
                                     '/takelogin/facebook' => [
                                         'view' => 'takelogin',
                                         'name' => 'takelogin_facebook',
                                     ],
                                 ]);
    }

    public function consoles()
    {
        return [
            CreateGodfather::class,
            CreateUserGroups::class,
        ];
    }

}