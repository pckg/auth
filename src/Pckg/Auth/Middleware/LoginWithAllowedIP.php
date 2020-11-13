<?php namespace Pckg\Auth\Middleware;

use Exception;
use Pckg\Api\Entity\AppKeys;
use Pckg\Auth\Service\Auth;
use Pckg\Mailo\Record\App;

class LoginWithAllowedIP
{

    public function execute(callable $next)
    {
        /**
         * Skip misconfigured requests.
         * Skip console requests.
         * Skip already logged in users.
         */
        $allowIps = config('pckg.auth.api.allowIps');
        if (!$allowIps || !isHttp() || auth()->isLoggedIn() || isConsole()) {
            return $next();
        }

        /**
         * Login when remote IP (+port) matches.
         * This is a userless / anonymous login?
         */
        $ip = request()->clientIp();
        $port = request()->clientPort();
        if (in_array($ip, $allowIps)) {
            auth()->setLoggedIn();
        } else if (in_array($ip . ':' . $port, $allowIps)) {
            auth()->setLoggedIn();
        }

        return $next();
    }

}