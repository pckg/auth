<?php

namespace Pckg\Auth\Event;

use Pckg\Auth\Event\Handler\LogUserLogin;
use Pckg\Concept\Event\AbstractEvent;

class UserLoggedIn extends AbstractEvent
{

    protected $name = 'user.loggedIn';

    public function __construct()
    {
        $this->addEventHandler(new LogUserLogin());
    }

}