<?php

namespace Pckg\Auth\Event;

use Pckg\Auth\Event\Handler\LogUserLogin;
use Pckg\Auth\Record\User;
use Pckg\Concept\Event\AbstractEvent;

class UserLoggedIn extends AbstractEvent
{

    protected $name = 'user.loggedIn';

    /**
     * @var User
     */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->addEventHandler(new LogUserLogin());
    }

    public function getEventData()
    {
        return [$this->user];
    }

}