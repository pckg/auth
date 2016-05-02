<?php

namespace Pckg\Auth\Record;

use Pckg\Database\Record;
use Pckg\Auth\Entity\Users;
use Pckg\Auth\Service\Auth;

/**
 * Class User
 * @package Pckg\Auth\Record
 */
class User extends Record
{

    protected $entity = Users::class;

}