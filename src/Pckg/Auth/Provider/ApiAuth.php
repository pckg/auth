<?php namespace Pckg\Auth\Provider;

use Pckg\Auth\Middleware\LoginWithApiKeyHeader;
use Pckg\Framework\Provider;

class ApiAuth extends Provider
{

    public function middlewares()
    {
        return [
            LoginWithApiKeyHeader::class,
        ];
    }

}