<?php namespace Pckg\Auth\Migration;

use Pckg\Migration\Migration;

class CreateAuthTables extends Migration
{

    public function up()
    {
        $this->userGroupsUp();
        $this->usersUp();

        $this->save();
    }

    protected function userGroupsUp()
    {
        $userGroups = $this->table('user_groups');
        $userGroups->title();
    }

    protected function usersUp()
    {
        $users = $this->table('users');
        $users->integer('user_group_id')->references('user_groups');
        $users->email()->unique();
        $users->password();
        $users->text('autologin');
        $users->deletable();
    }

}