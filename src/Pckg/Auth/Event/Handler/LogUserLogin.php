<?php

namespace Pckg\Auth\Event\Handler;

use Pckg\Auth\Entity\Logins;
use Pckg\Auth\Record\Login;
use Pckg\Auth\Record\User;
use Pckg\Concept\AbstractChainOfReponsibility;

/**
 * Class LogUserLogin
 *
 * @package Pckg\Auth\Event\Handler
 */
class LogUserLogin extends AbstractChainOfReponsibility
{

    /**
     * @param  User     $user
     * @param  callable $next
     * @return mixed
     */
    public function handle(User $user, callable $next)
    {
        if (!(new Logins())->getRepository()->getCache()->hasTable('logins')) {
            return $next();
        }

        Login::create(
            [
                'hash' => $_SESSION['Auth']['hash'] ?? null,
                'user_id' => $user->id,
                'datetime' => date('Y-m-d H:i:s'),
            ]
        );

        return $next();
    }
}
