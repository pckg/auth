<?php namespace Pckg\Auth\Migration;

use Pckg\Migration\Migration;

class CreatePermissionsTable extends Migration
{

    public function up()
    {
        $permissions = $this->table('permissions');
        $permissions->integer('record_id');
    }

}
