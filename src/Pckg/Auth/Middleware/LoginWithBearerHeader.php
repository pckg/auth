<?php namespace Pckg\Auth\Middleware;

use Exception;
use OpenCode\OAuth2\Service\OAuth2Server;
use Pckg\Api\Entity\AppKeys;
use Pckg\Auth\Service\Auth;
use Pckg\Mailo\Record\App;

class LoginWithBearerHeader
{

    public function execute(callable $next)
    {
        /**
         * Skip misconfigured requests.
         * Skip console requests.
         * Skip already logged in users.
         */
        $headerName = config('pckg.auth.bearerHeader');
        if (!$headerName || !isHttp() || isConsole() || auth()->isLoggedIn()) {
            return $next();
        }

        /**
         * Process request with header.
         */
        $headers = request()->getHeaders();
        if (!array_key_exists($headerName, $headers)) {
            return $next();
        }

        /**
         * Check that header is set.
         */
        $apiKey = $headers[$headerName];
        if (!$apiKey) {
            return $next();
        }

        try {
            /**
             * @var $server OAuth2Server
             */
            $server = resolve(OAuth2Server::class);
        } catch (\Throwable $e) {
            return $next();
        }

        /**
         * Validate OAuth2 request.
         */
        $request = $server->getResourceServer()->validateAuthenticatedRequest(request());

        /**
         * Check that parameter is set.
         */
        $userId = $request->getAttribute('oauth_user_id');
        if (!$userId) {
            return $next();
        }

        /**
         * Check that user exists.
         */
        $user = auth()->getProvider()->getUserById($userId);
        if (!$user) {
            return $next();
        }

        /**
         * Authenticating user with Bearer header.
         */
        auth()->performLogin($user);

        return $next();
    }

}