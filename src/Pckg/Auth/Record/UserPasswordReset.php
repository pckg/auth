<?php namespace Pckg\Auth\Record;

use Pckg\Auth\Entity\UserPasswordResets;
use Pckg\Database\Record;

class UserPasswordReset extends Record
{

    protected $entity = UserPasswordResets::class;

    public function hasRequestedTooSoon()
    {
        return time() - strtotime($this->created_at) < (60 * 5);
    }

}