<?php

namespace Pckg\Auth\Record;

use Pckg\Auth\Entity\UserGroups;
use Pckg\Database\Record;

class UserGroup extends Record
{

    protected $entity = UserGroups::class;

    const MODE_SUPER = 'SUPER';

    const MODE_GUEST = 'GUEST';

    public function isSuper()
    {
        return $this->mode == static::MODE_SUPER;
    }

    public function isGuest()
    {
        return $this->mode == static::MODE_GUEST;
    }

}