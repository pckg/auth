<?php namespace Pckg\Auth\Migration;

use Pckg\Migration\Migration;

class CreateAuthTables extends Migration
{

    public function up()
    {
        $this->userGroupsUp();
        $this->usersUp();
    }

    protected function userGroupsUp()
    {
        $userGroups = $this->table('user_groups');
        $userGroups->slug();

        $userGroupsI18n = $this->translatable('user_groups');
        $userGroupsI18n->title();
    }

    protected function usersUp()
    {
        $users = $this->table('users');
        $users->integer('user_group_id')->references('user_groups');
        $users->email();
        $users->password();
    }

}