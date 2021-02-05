<?php

namespace Pckg\Auth\Entity;

use Pckg\Auth\Record\User;
use Pckg\Database\Entity;

/**
 * Class Users
 *
 * @package Pckg\Auth\Entity
 * @method  $this withStaticGroup()
 * @method  $this joinStaticGroup()
 * @method  $this withRequiredStaticGroup()
 */
class Users extends Entity
{

    /**
     * @var string
     */
    protected $record = User::class;

    /**
     * @param  $email
     * @param  $password
     * @return mixed|\Pckg\Database\Record|null
     */
    public function getUserByEmailAndPassword($email, $password)
    {
        return $this->where('email', $email)
            ->where('password', $password)
            ->one();
    }

    /**
     * @return \Pckg\Database\Relation\HasMany
     */
    public function logins()
    {
        return $this->hasMany(Logins::class)
            ->foreignKey('user_id');
    }
}
