<?php

namespace Pckg\Auth\Middleware;

use Pckg\Auth\Service\Auth;
use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Framework\Response;

class LoginWithCookie extends AbstractChainOfReponsibility
{

    public function execute(callable $next)
    {
        if (!isHttp() || auth()->isLoggedIn() || !auth()->getSecureCookie(Auth::COOKIE_AUTOLOGIN) /*!request()->isGet() || */
        ) {
            return $next();
        }

        auth()->performAutologin();

        return $next();
    }

}