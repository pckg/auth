<?php

namespace Pckg\Auth\Event;

use Pckg\Concept\Event\Event;

/**
 * Class SendUserRegistrationEmail
 *
 * @package Pckg\User\Event
 */
class UserRegistered
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
        // $mail = Mail::createFromTemplate('user/userRegistered');
        // $mail->setRecepient($this->event->user->getEmail());
        // $mail->send();
    }
}
