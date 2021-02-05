<?php namespace Pckg\Auth\Provider;

use Pckg\Auth\Middleware\LoginWithApiKeyHeader;
use Pckg\Framework\Provider;

/**
 * Class ApiAuth
 * @package Pckg\Auth\Provider
 */
class ApiAuth extends Provider
{

    /**
     * @return string[]
     */
    public function middlewares()
    {
        return [
            LoginWithApiKeyHeader::class,
        ];
    }

}