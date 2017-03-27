<?php

namespace Pckg\Auth\Middleware;

use Pckg\Auth\Command\LoginUser;
use Pckg\Framework\Response;

class HandleLoginRequest
{

    public function execute(callable $next)
    {
        if (post()->has(['email', 'password', 'autologin', 'submit'])
            && server()->has(['HTTP_REFERER', 'HTTP_ORIGIN', 'REQUEST_URI'])
            && server('HTTP_REFERER') != server('HTTP_ORIGIN') . server('REQUEST_URI')
        ) {
            $loginUser = new LoginUser();
            $loginUser->onSuccess(
                function() {
                    response()->respondWithSuccessRedirect(auth('frontend')->getUser()->getDashboardUrl());
                    redirect(-1);
                }
            );
            $loginUser->executeManual(post('email'), post('password'), post('autologin', false));
        }

        return $next();
    }

}