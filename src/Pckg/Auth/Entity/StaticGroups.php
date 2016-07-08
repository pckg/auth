<?php

namespace Pckg\Auth\Entity;

use Pckg\Database\Entity;

class StaticGroups extends Entity
{

    public function all()
    {
        return [
            [
                'id'    => 1,
                'title' => 'Admin',
            ],
            [
                'id'    => 2,
                'title' => 'SysOp',
            ],
        ];
    }

    public function one()
    {
        return [
            [
                'id'    => 1,
                'title' => 'Admin',
            ],
        ];
    }

}