<?php

namespace Pckg\Auth\Event\Handler;

use Pckg\Auth\Record\Login;
use Pckg\Auth\Record\User;
use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Framework\Request\Data\Session;

class LogUserLogin extends AbstractChainOfReponsibility
{

    public function handle(User $rUser, Login $rLogin, Session $session)
    {
        Login::create([
                          'hash'    => $_SESSION['Auth']['hash'],
                          'ip'      => $_SERVER['REMOTE_ADDR'],
                          'dt_in'   => date('Y-m-d H:i:s'),
                          'user_id' => $rUser->id,
                      ]);
    }

}