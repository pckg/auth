<?php namespace Pckg\Auth\Middleware;

use Exception;
use Pckg\Pendo\Record\AppKey;

class RegisterApiKeyHeader
{

    public function execute(callable $next)
    {
        /**
         * Skip misconfigured requests.
         * Skip console requests.
         * Skip already logged in users.
         */
        $headerName = config('pckg.auth.apiHeader');
        if (!$headerName || !isHttp() || !auth()->isLoggedIn() || isConsole()) {
            return $next();
        }
        $headers = getallheaders();
        $apiKey = $headers[$headerName] ?? null;

        if (!$apiKey) {
            return $next();
        }

        $appKey = AppKey::gets(['key' => $apiKey, 'valid' => true]);
        if (!$appKey) {
            throw new Exception('Invalid or inactive api key');
        }

        context()->bind(AppKey::class, $appKey);

        return $next();
    }

}