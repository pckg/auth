<?php

namespace Pckg\Auth\Event\Handler;

use Pckg\Auth\Record\User;
use Pckg\Auth\Service\Auth;
use Pckg\Concept\AbstractChainOfReponsibility;

class CheckAdminLogin extends AbstractChainOfReponsibility
{

    public function handle(User $user)
    {
        if (!$user->isAdmin()) {
            return;
        }

        trigger(Auth::class . '.adminLoggedIn', [$user]);
    }

}