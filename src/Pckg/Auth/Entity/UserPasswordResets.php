<?php

namespace Pckg\Auth\Entity;

use Pckg\Auth\Record\UserPasswordReset;
use Pckg\Database\Entity;

/**
 * Class UserPasswordResets
 *
 * @package Pckg\Auth\Entity
 */
class UserPasswordResets extends Entity
{
    /**
     * @var string
     */
    protected $record = UserPasswordReset::class;

    /**
     * @return \Pckg\Database\Relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(Users::class)->foreignKey('user_id');
    }
}
