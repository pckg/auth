<?php

namespace Pckg\Auth\Event;

use Pckg\Concept\Event\AbstractEvent;
use Pckg\Auth\Event\Handler\LogUserLogin;

class UserLoggedIn extends AbstractEvent
{

    protected $name = 'user.loggedIn';

    public function __construct()
    {
        $this->addEventHandler(new LogUserLogin());
    }

}