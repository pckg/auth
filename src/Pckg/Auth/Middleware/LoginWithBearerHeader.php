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
        $headerName = 'Authorization';
        if (!$headerName || !isHttp() || auth()->isLoggedIn() || isConsole()) {
            return $next();
        }

        /**
         * Process request with header.
         */
        $headers = request()->getHeaders();
        if (!array_key_exists($headerName, $headers)) {
            return $next();
        }

        $apiKey = $headers[$headerName];
        if (!$apiKey) {
            return $next();
        }

        /**
         * @var $server OAuth2Server
         */
        $server = resolve(OAuth2Server::class);
        $request = $server->getResourceServer()->validateAuthenticatedRequest(request());
        
        $userId = $request->getAttribute('oauth_user_id');
        if (!$userId) {
            throw new Exception('Invalid Bearer User');
        }
        $user = auth()->getProvider()->getUserById($userId);
        auth()->performLogin($user);

        /**
         * Authenticating user with api key.
         */
        return $next();
    }

}