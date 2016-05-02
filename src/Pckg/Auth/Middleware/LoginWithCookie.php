<?php

namespace Pckg\Auth\Middleware;

use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Framework\Request;

class LoginWithCookie extends AbstractChainOfReponsibility
{

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function execute(callable $next)
    {
        if ($this->request->isGet() && isset($_COOKIE['autologin'])) {
            var_dump(static::class, $_COOKIE['autologin']);
            die('@T00D0');
        }

        return $next();
    }

}