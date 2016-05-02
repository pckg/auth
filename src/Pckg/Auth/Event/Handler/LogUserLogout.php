<?php

namespace Pckg\Auth\Event\Handler;

use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Concept\AbstractObject;

class LogUserLogout extends AbstractChainOfReponsibility
{

    public function handle(callable $next, AbstractObject $handler)
    {
        die("writing logout log");

        return $next();
    }

}