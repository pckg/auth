<?php

namespace Pckg\Auth\Middleware;

use Exception;
use Pckg\Api\Entity\AppKeys;
use Pckg\Auth\Service\Auth;
use Pckg\Mailo\Record\App;

/**
 * Class LoginWithApiKeyHeader
 *
 * @package Pckg\Auth\Middleware
 */
class LoginWithApiKeyHeader
{
    /**
     * @param  callable $next
     * @return mixed
     */
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
        $apiKey = request()->header($headerName);
        if (!$apiKey) {
            return $next();
        }

        /**
         * Authenticating user with api key.
         */
        $entity = config('pckg.auth.appEntity', AppKeys::class);
        $field = config('pckg.auth.apiEntityField', 'key');
        $entity = new $entity();
        $token = $entity->where($field, $apiKey)->where('valid')->oneOrFail(
            function () {
                response()->forbidden('Invalid API key');
            }
        );

        /**
         * Authenticate user
         */
        $auth = auth();
        $user = auth()->getProvider()->getUserById($token->app->user_id);
        $auth->authenticate($user);
        context()->bind(Auth::class . ':api', $token);

        return $next();
    }
}
