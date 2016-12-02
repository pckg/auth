<?php

namespace Pckg\Auth\Middleware;

use Pckg\Auth\Command\LoginUser;
use Pckg\Framework\Response;

class HandleLoginRequest
{

    public function execute(callable $next)
    {
        if (post()->has(['email', 'password', 'autologin', 'submit'])) {
            $loginUser = new LoginUser();
            $loginUser->onSuccess(
                function() {
                    redirect(-1);
                }
            );
            $loginUser->executeManual(post('email'), post('password'));
        }

        return $next();
    }

}