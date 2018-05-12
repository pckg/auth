<?php namespace Pckg\Auth\Event\Handler;

use Pckg\Auth\Entity\Logins;
use Pckg\Auth\Record\Login;
use Pckg\Auth\Record\User;
use Pckg\Concept\AbstractChainOfReponsibility;

class LogUserLogin extends AbstractChainOfReponsibility
{

    public function handle(User $user, callable $next)
    {
        if (!(new Logins())->getRepository()->getCache()->hasTable('logins')) {
            return $next();
        }

        Login::create([
            'hash'     => $_SESSION['Auth']['hash'] ?? null,
            'user_id'  => $user->id,
            'datetime' => date('Y-m-d H:i:s'),
            'ip'       => server('REMOTE_ADDR', null),
        ]);

        return $next();
    }

}