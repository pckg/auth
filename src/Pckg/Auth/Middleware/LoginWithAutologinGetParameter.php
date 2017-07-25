<?php namespace Pckg\Auth\Middleware;

use Pckg\Auth\Entity\Users;
use Pckg\Auth\Record\User;

class LoginWithAutologinGetParameter
{

    public function execute(callable $next)
    {
        /**
         * Skip misconfigured requests.
         * Skip console requests.
         * Skip already logged in users.
         */
        $headerName = config('pckg.auth.getParameter');
        if (!$headerName || !get($headerName) || !isHttp() || auth()->isLoggedIn()) {
            return $next();
        }

        /**
         * Process request with header.
         */
        $autologin = get($headerName);
        if ($autologin) {
            /**
             * Authenticating user with autologin.
             */
            (new Users())->where('autologin', $autologin)
                         ->oneAndIf(function(User $user) {
                             auth()->autologin($user->id);
                         });
        }

        return $next();
    }

}