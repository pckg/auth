<?php

namespace Pckg\Auth\Middleware;

use Pckg\Auth\Service\Auth;
use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Framework\Response;

/**
 * Class LoginWithCookie
 *
 * @package Pckg\Auth\Middleware
 */
class LoginWithCookie extends AbstractChainOfReponsibility
{
    /**
     * @param  callable $next
     * @return mixed
     */
    public function execute(callable $next)
    {
        if (!isHttp() || !auth()->getSecureCookie(Auth::COOKIE_AUTOLOGIN) || auth()->isLoggedIn()) {
            return $next();
        }

        auth()->performAutologin();

        return $next();
    }
}
