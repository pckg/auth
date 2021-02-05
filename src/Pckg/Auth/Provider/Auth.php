<?php namespace Pckg\Auth\Provider;

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

/**
 * Class Auth
 *
 * @package Pckg\Auth\Provider
 */
class Auth extends Provider
{

    /**
     * @return string[]
     */
    public function providers()
    {
        return [
            BasicAuth::class,
            FacebookAuth::class,
        ];
    }

    /**
     * @return string[]
     */
    public function listeners()
    {
        return [
            \Pckg\Auth\Service\Auth::class . '.userLoggedIn' => UserLoggedIn::class,
            'user.registered' => UserRegistered::class,
        ];
    }

    /**
     * @return array
     */
    public function path()
    {
        return [
            'view' => realpath(__DIR__ . '/../View'),
        ];
    }

    /**
     * @return string[]
     */
    public function viewObjects()
    {
        return [
            '_auth' => AuthService::class,
            //'_user' => SessionUser::class,
        ];
    }

    /**
     * @return array
     */
    public function routes()
    {
        return [
            'url' => array_merge_array(
                [
                    'controller' => AuthController::class,
                ], [
                    '/login' => [
                        'view' => 'login',
                        'name' => 'login',
                        'tags' => [
                            'auth:out',
                            'layout:frontend',
                            'seo:title' => 'Login',
                            'vue:route',
                            'vue:route:template' => config('pckg.auth.component.login', '<pckg-auth-full></pckg-auth-full>'),
                            'container' => '--lg',
                        ],
                    ],
                    '/logout' => [
                        'view' => 'logout',
                        'name' => 'pckg.auth.logout',
                    ],
                    '/api/auth/signup' => [
                        'view' => 'signup',
                    ],
                    '/activate-user/[activation]' => [
                        'view' => 'activate',
                        'name' => 'pckg.auth.activate',
                    ],
                    '/forgot-password' => [
                        'view' => 'forgotPassword',
                        'name' => 'pckg.auth.forgotPassword',
                    ],
                    '/password-code' => [
                        'view' => 'passwordCode',
                        'name' => 'pckg.auth.passwordCode',
                    ],
                    '/reset-password' => [
                        'view' => 'resetPassword',
                        'name' => 'pckg.auth.resetPassword',
                    ],
                    '/api/auth/user' => [
                        'view' => 'user',
                        'name' => 'api.auth.user',
                    ],
                    '/api/auth/user/addresses' => [
                        'view' => 'userAddresses',
                        'name' => 'api.auth.user.addresses',
                    ],
                    '/oauth/[provider]/resource' => [
                        'view' => 'me',
                        'name' => 'oauth.provider.me',
                    ],
                    '/oauth/[provider]' => [
                        'view' => 'oauth',
                        'name' => 'oauth.provider',
                    ],
                ]
            )
        ];
    }

}