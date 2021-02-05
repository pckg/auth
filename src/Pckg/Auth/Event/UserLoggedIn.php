<?php

namespace Pckg\Auth\Event;

use Pckg\Auth\Event\Handler\LogUserLogin;
use Pckg\Auth\Record\User;
use Pckg\Concept\Event\AbstractEvent;

/**
 * Class UserLoggedIn
 * @package Pckg\Auth\Event
 */
class UserLoggedIn extends AbstractEvent
{

    /**
     * @var string
     */
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

    /**
     * @return User[]
     */
    public function getEventData()
    {
        return [$this->user];
    }

}