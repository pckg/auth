<?php namespace Impero\User\Middleware;

use Exception;
use Impero\User\Entity\UserTokens;

class LoginWithApiKeyHeader
{

    public function execute(callable $next)
    {
        /**
         * Skip misconfigured requests.
         */
        $headerName = config('pckg.auth.apiHeader');
        if (!$headerName) {
            return $next();
        }

        /**
         * Skip console requests.
         */
        if (!isHttp()) {
            return $next();
        }

        /**
         * Skipp already logged in users.
         */
        if (auth()->isLoggedIn()) {
            return $next();
        }

        /**
         * Process request with header.
         */
        $headers = getallheaders();
        if ($apiKey = $headers[$headerName] ?? null) {
            /**
             * Authenticating user with api key.
             */
            $token = (new UserTokens())->where('token', $apiKey)->one();

            if (!$token) {
                throw new Exception('Invalid token');
            }

            auth()->autologin($token->user_id);
        }

        return $next();
    }

}