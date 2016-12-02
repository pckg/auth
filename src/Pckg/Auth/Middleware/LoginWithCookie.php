<?php

namespace Pckg\Auth\Middleware;

use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Framework\Response;

class LoginWithCookie extends AbstractChainOfReponsibility
{

    public function execute(callable $next)
    {
        if (auth()->isLoggedIn() || !request()->isGet() || !isset($_COOKIE['pckg_auth_autologin'])
        ) {
            return $next();
        }

        $cookie = unserialize($_COOKIE['pckg_auth_autologin']);
        foreach ($cookie as $provider => $data) {
            if (isset($data['user_id']) && isset($data['hash'])
                && sha1(config('security.hash') . $data['user_id']) == $data['hash']
            ) {
                auth()->useProvider($provider);
                auth()->autologin($data['user_id']);
            }
        }

        return $next();
    }

}