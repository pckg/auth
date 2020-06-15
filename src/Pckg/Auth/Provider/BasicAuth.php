<?php namespace Pckg\Auth\Provider;

use Pckg\Auth\Console\CreateGodfather;
use Pckg\Auth\Console\CreateUser;
use Pckg\Auth\Console\CreateUserGroups;
use Pckg\Auth\Middleware\HandleLoginRequest;
use Pckg\Auth\Middleware\HandleLogoutRequest;
use Pckg\Auth\Middleware\LoginWithApiKeyHeader;
use Pckg\Auth\Middleware\LoginWithAutologinGetParameter;
use Pckg\Auth\Middleware\LoginWithCookie;
use Pckg\Auth\Middleware\RestrictAccess;
use Pckg\Framework\Provider;

/**
 * Class BasicAuth
 * @package Pckg\Auth\Provider
 */
class BasicAuth extends Provider {

    /**
     * @return string[]
     */
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

    /**
     * @return string[]
     */
    public function consoles()
    {
        return [
            CreateUserGroups::class,
            CreateGodfather::class,
            CreateUser::class,
        ];
    }
    
}