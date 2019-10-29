<?php namespace Pckg\Auth\Middleware;

use Exception;
use Pckg\Api\Entity\AppKeys;
use Pckg\Mailo\Record\App;

class LoginWithApiKeyHeader
{

    public function execute(callable $next)
    {
        /**
         * Skip misconfigured requests.
         * Skip console requests.
         * Skip already logged in users.
         */
        $headerName = config('pckg.auth.apiHeader');
        if (!$headerName || !isHttp() || auth()->isLoggedIn() || isConsole()) {
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
            $entity = config('pckg.auth.appEntity', AppKeys::class);
            $token = (new $entity)->where('key', $apiKey)->where('valid')->one();

            if (!$token) {
                throw new Exception('Invalid token');
            }

            auth()->autologin($token->app->user_id);
            context()->bind(App::class, $token->app);
        }

        return $next();
    }

}