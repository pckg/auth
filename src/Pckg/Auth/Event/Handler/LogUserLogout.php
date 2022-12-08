<?php

namespace Pckg\Auth\Event\Handler;

use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Concept\AbstractObject;

/**
 * Class LogUserLogout
 *
 * @package Pckg\Auth\Event\Handler
 */
class LogUserLogout extends AbstractChainOfReponsibility
{
    /**
     * @param  callable       $next
     * @param  AbstractObject $handler
     * @return mixed
     */
    public function handle(callable $next, AbstractObject $handler)
    {
        error_log(static::class);

        return $next();
    }
}
