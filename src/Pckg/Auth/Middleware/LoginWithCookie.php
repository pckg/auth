<?php

namespace Pckg\Auth\Middleware;

use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Framework\Response;

class LoginWithCookie extends AbstractChainOfReponsibility
{

    public function execute(callable $next)
    {
        if (!isHttp() || auth()->isLoggedIn() || !isset($_COOKIE['pckg_auth_autologin']) /*!request()->isGet() || */
        ) {
            return $next();
        }

        auth()->performAutologin();

        return $next();
    }

}