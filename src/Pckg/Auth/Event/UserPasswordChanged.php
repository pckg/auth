<?php

namespace Pckg\Auth\Event;

use Pckg\Concept\Event\Event;

/**
 * Class UserPasswordChanged
 *
 * @package Pckg\Auth\Event
 */
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
