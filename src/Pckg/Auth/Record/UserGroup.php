<?php

namespace Pckg\Auth\Record;

use Pckg\Auth\Entity\UserGroups;
use Pckg\Database\Record;

/**
 * Class UserGroup
 *
 * @package  Pckg\Auth\Record
 * @property string $mode
 */
class UserGroup extends Record
{

    /**
     * @var string
     */
    protected $entity = UserGroups::class;

    /**
     *
     */
    const MODE_SUPER = 'SUPER';

    /**
     *
     */
    const MODE_GUEST = 'GUEST';

    /**
     * @return bool
     */
    public function isSuper()
    {
        return $this->mode == static::MODE_SUPER;
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return $this->mode == static::MODE_GUEST;
    }

}