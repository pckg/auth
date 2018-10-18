<?php namespace Pckg\Auth\Entity;

use Pckg\Auth\Record\UserPasswordReset;
use Pckg\Database\Entity;

class UserPasswordResets extends Entity
{

    protected $record = UserPasswordReset::class;

    public function user()
    {
        return $this->belongsTo(Users::class)->foreignKey('user_id');
    }

}