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
        if (!array_key_exists($headerName, $headers)) {
            return $next();
        }

        $apiKey = $headers[$headerName];
        if (!$apiKey) {
            response()->forbidden('Missing API key');
        }

        /**
         * Authenticating user with api key.
         */
        $entity = config('pckg.auth.appEntity', AppKeys::class);
        $field = config('pckg.auth.apiEntityField', 'key');
        $entity = new $entity;
        $token = $entity->where($field, $apiKey)->where('valid')->oneOrFail(function() use ($entity, $field) {
            response()->forbidden('Invalid API key');
        });

        /**
         * Authenticate user
         */
        auth()->authenticate($token->app->user);

        return $next();
    }

}