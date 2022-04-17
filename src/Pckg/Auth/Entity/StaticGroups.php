<?php

namespace Pckg\Auth\Entity;

use Pckg\Database\Entity;

/**
 * Class StaticGroups
 *
 * @package Pckg\Auth\Entity
 */
class StaticGroups extends Entity
{
    /**
     * @return array[]
     */
    public function all()
    {
        return [
            [
                'id' => 1,
                'title' => 'Admin',
            ],
            [
                'id' => 2,
                'title' => 'SysOp',
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function one()
    {
        return [
            [
                'id' => 1,
                'title' => 'Admin',
            ],
        ];
    }
}
