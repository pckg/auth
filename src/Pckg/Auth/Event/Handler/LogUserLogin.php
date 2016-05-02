<?php

namespace Pckg\Auth\Event\Handler;

use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Framework\Request\Data\Session;
use Pckg\Auth\Record\User;

class LogUserLogin extends AbstractChainOfReponsibility
{

    public function handle(User $rUser, Login $rLogin, Session $session)
    {
        $rLogin->setHash($_SESSION['Auth']['hash']);
        $rLogin->setIp($_SERVER['REMOTE_ADDR']);
        $rLogin->setDtIn(date('Y-m-d H:i:s'));
        $rLogin->setUserId($rUser->getId());

        if (!$rLogin->save()) {
            return false;
        }

        return $this->next->handle($rUser, $rLogin);
    }

}