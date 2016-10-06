<?php

namespace Pckg\Auth\Event;

use Symfony\Component\EventDispatcher\Event;

class UserPasswordChanged
{

    /**
     * @var Event
     */
    protected $event;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     *
     */
    public function handle()
    {
    }

}