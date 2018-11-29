<?php

namespace Pckg\Auth\Middleware;

use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Framework\Response;

class LoginWithCookie extends AbstractChainOfReponsibility
{

    public function execute(callable $next)
    {
        if (!isset($_COOKIE['pckg_auth_autologin']) || isConsole() || !request()->isGet() || auth()->isLoggedIn()
        ) {
            return $next();
        }

        auth()->performAutologin();

        return $next();
    }

}