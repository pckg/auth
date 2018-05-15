<?php namespace Pckg\Auth\Console;

use Exception;
use Pckg\Auth\Entity\Users;
use Pckg\Auth\Record\User;
use Pckg\Auth\Record\UserGroup;
use Pckg\Framework\Console\Command;

class CreateGodfather extends Command
{

    protected function configure()
    {
        $this->setName('auth:create-godfather')
             ->setDescription('Create godfather user')
             ->addArguments(
                 [
                     'email' => 'Godfather email',
                     'password' => 'Hashed godfather password',
                 ]
             );
    }

    public function handle()
    {
        $totalUsers = (new Users())->limit(1)->total();
        if ($totalUsers) {
            throw new Exception("There are already users in database");
        }

        $statuses = ['Super admin', 'User', 'Administrator', 'PR', 'Checkin'];
        foreach ($statuses as $status) {
            UserGroup::create(['title' => $status]);
        }

        $user = (new User(
            ['email' => $this->argument('email'), 'password' => $this->argument('password')]
        ))->setDefaults()->setAndSave(['user_group_id' => 1]);

        $this->output('Godfather #' . $user->id . ' created');
    }

}