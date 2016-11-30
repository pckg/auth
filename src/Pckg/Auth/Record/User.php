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
        return in_array($this->user_group_id, [1, 2, 3]);
    }

}