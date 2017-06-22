<?php

namespace Pckg\Auth\Record;

use Pckg\Auth\Entity\Users;
use Pckg\Database\Record;

/**
 * Class User
 *
 * @package Pckg\Auth\Record
 */
class User extends Record
{

    protected $entity = Users::class;

    public function isAdmin()
    {
        return in_array($this->status_id, [1]);
    }

    public function isCheckin()
    {
        return in_array($this->status_id, [5]);
    }

}