<?php

namespace Pckg\Auth\Entity;

use Pckg\Auth\Record\UserGroup;
use Pckg\Collection;
use Pckg\Database\Entity;

/**
 * Class StaticGroups
 *
 * @package Pckg\Auth\Entity
 */
class StaticGroups extends Entity
{
    /**
     * @return Collection
     */
    public function all()
    {
        return collect([
            [
                'id' => 1,
                'title' => 'Admin',
            ],
            [
                'id' => 2,
                'title' => 'SysOp',
            ],
        ]);
    }

    public function one()
    {
        return new UserGroup([
            'id' => 1,
            'title' => 'Admin',
        ]);
    }
}
