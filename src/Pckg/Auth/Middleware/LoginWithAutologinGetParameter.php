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

        if (!$headerName || !isHttp() || auth()->isLoggedIn()) {
            return $next();
        }

        /**
         * Process request with header.
         */
        $autologin = get($headerName);
        if (!$autologin || !is_string($autologin)) {
            return $next();
        }

        /**
         * Authenticating user with autologin.
         */
        (new Users())->where('autologin', $autologin)
                     ->oneAndIf(
                         function(User $user) use ($headerName) {
                             auth()->autologin($user->id);
                         }
                     );

        /**
         * Remove autologin parameter and redirect to same url.
         */
        $get = get()->all();
        unset($get[$headerName]);
        $query = http_build_query($get);
        $url = request()->getUrl();
        redirect($url . ($query ? '?' . $query : ''));

        return $next();
    }

}