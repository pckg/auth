<?php

namespace Pckg\Auth\Event;

use Pckg\Concept\Event\EventHandler;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class SendUserRegistrationEmail
 *
 * @package Pckg\User\Event
 */
class UserRegistered implements EventHandler
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