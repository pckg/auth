<?php

namespace Pckg\Auth\Middleware;

use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Framework\Response;

class LoginWithCookie extends AbstractChainOfReponsibility
{

    public function execute(callable $next)
    {
        if (isConsole() || auth()->isLoggedIn() || !request()->isGet() || !isset($_COOKIE['pckg_auth_autologin'])
        ) {
            return $next();
        }

        auth()->performAutologin();

        return $next();
    }

}