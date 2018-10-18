<?php namespace Pckg\Auth\Record;

use Pckg\Auth\Entity\UserPasswordResets;
use Pckg\Database\Record;

class UserPasswordReset extends Record
{

    protected $entity = UserPasswordResets::class;

}