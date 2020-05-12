<?php namespace Pckg\Auth\Migration;

use Pckg\Migration\Migration;

class CreateAuthTables extends Migration
{

    public function up()
    {
        $this->userGroupsUp();
        $this->usersUp();
        $this->userPasswordResetsUp();

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

    protected function userPasswordResetsUp()
    {
        $userPasswordResets = $this->table('user_password_resets');
        $userPasswordResets->integer('user_id')->references('users');
        $userPasswordResets->datetime('created_at');
        $userPasswordResets->datetime('used_at');
        $userPasswordResets->varchar('code');
    }

}