<?php

namespace Pckg\Auth\Middleware;

use Pckg\Auth\Service\Auth;
use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Framework\Response;

class LoginWithCookie extends AbstractChainOfReponsibility
{

    public function execute(callable $next)
    {
        if (!isHttp() || !request()->isGet() || !auth()->getSecureCookie(Auth::COOKIE_AUTOLOGIN) || auth()->isLoggedIn()) {
            return $next();
        }

        auth()->performAutologin();

        return $next();
    }

}