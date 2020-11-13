<?php

namespace Pckg\Auth\Middleware;

use Pckg\Auth\Service\Auth;
use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Framework\Response;

class LoginWithSession extends AbstractChainOfReponsibility
{

    public function execute(callable $next)
    {
        if (!isHttp() || !request()->isGet() || auth()->isLoggedIn()) {
            return $next();
        }

        /**
         * Prepare requirements.
         */
        $auth = auth();
        $auth->requestProvider();
        $providerKey = $auth->getProviderKey();
        $sessionProvider = $auth->getSessionProvider();

        /**
         * Session for provider does not exist.
         */
        if (!$sessionProvider) {
            return $next();
        }

        /**
         * Session exists, but user doesn't.
         */
        if (!isset($sessionProvider['user']['id'])) {
            return $next();
        }

        /**
         * Cookie for provider does not exist.
         */
        $cookieKey = Auth::COOKIE_PROVIDER . '_' . $providerKey;
        $cookie = $auth->getSecureCookie($cookieKey);
        if (!$cookie) {
            return $next();
        }

        /**
         * Cookie exists, but hash isn't set.
         */
        if (!isset($cookie['hash'])) {
            return $next();
        }

        /**
         * Hash and user matches.
         */
        if ($cookie['hash'] != $sessionProvider['hash'] || $cookie['user'] != $sessionProvider['user']['id']) {
            return $next();
        }

        /**
         * User exists in database.
         */
        $user = $auth->getProvider()->getUserById($sessionProvider['user']['id']);

        if (!$user) {
            return $next();
        }

        /**
         * Validate session signature.
         */
        $userSecuritySessionPass = $auth->getUserSecuritySessionPass($user);
        if (!password_verify($userSecuritySessionPass, $sessionProvider['hash'])) {
            $auth->setUser(null);
            $auth->setLoggedIn(false);
            return $next();
        }

        /**
         * Invalidate user.
         */
        $auth->setUser($user);
        $auth->setLoggedIn();

        return $next();
    }

}