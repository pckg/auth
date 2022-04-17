<?php

namespace Pckg\Auth\Migration;

use Pckg\Migration\Migration;

/**
 * Class CreatePermissionsTable
 *
 * @package Pckg\Auth\Migration
 */
class CreatePermissionsTable extends Migration
{
    /**
     * @return CreatePermissionsTable|void
     */
    public function up()
    {
        $permissions = $this->table('permissions');
        $permissions->integer('record_id');
    }
}
