<?php

namespace Pckg\Auth\Record;

use Pckg\Auth\Entity\UserPasswordResets;
use Pckg\Database\Record;

/**
 * Class UserPasswordReset
 *
 * @package  Pckg\Auth\Record
 * @property string $created_at
 */
class UserPasswordReset extends Record
{

    /**
     * @var string
     */
    protected $entity = UserPasswordResets::class;

    /**
     * @return bool
     */
    public function hasRequestedTooSoon()
    {
        return time() - strtotime($this->created_at) < (60 * 5);
    }
}
