<?php

namespace Pckg\Auth\Entity;

use Pckg\Database\Entity;
use Pckg\Auth\Record\User;

/**
 * Class Users
 * @package Pckg\Auth\Entity
 * @method $this withStaticGroup()
 * @method $this joinStaticGroup()
 * @method $this withRequiredStaticGroup()
 */
class Users extends Entity
{

    /**
     * @var string
     */
    protected $record = User::class;

    public function getUserByEmailAndPassword($email, $password)
    {
        return $this->where('email', $email)
            ->where('password', $password)
            ->one();
    }
}