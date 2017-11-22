<?php

namespace Pckg\Auth\Event\Handler;

use Pckg\Auth\Record\Login;
use Pckg\Auth\Record\User;
use Pckg\Concept\AbstractChainOfReponsibility;

class LogUserLogin extends AbstractChainOfReponsibility
{

    public function handle(User $user, callable $next)
    {
        Login::create([
                          'hash'    => $_SESSION['Auth']['hash'] ?? null,
                          'ip'      => $_SERVER['REMOTE_ADDR'],
                          'dt_in'   => date('Y-m-d H:i:s'),
                          'user_id' => $user->id,
                      ]);

        return $next();
    }

}