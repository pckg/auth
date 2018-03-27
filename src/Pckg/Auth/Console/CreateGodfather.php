<?php namespace Pckg\Auth\Console;

use Exception;
use Pckg\Auth\Entity\Users;
use Pckg\Auth\Record\User;
use Pckg\Database\Entity;
use Pckg\Database\Record;
use Pckg\Framework\Console\Command;

class CreateGodfather extends Command
{

    protected function configure()
    {
        $this->setName('auth:create-godfather')
             ->setDescription('Create godfather user')
             ->addArguments([
                                'email' => 'Godfather email',
                            ]);
    }

    public function handle()
    {
        $totalUsers = (new Users())->limit(1)->total();
        if ($totalUsers) {
            throw new Exception("There are already users in database");
        }

        $statuses = ['Super admin', 'User', 'Administrator', 'PR', 'Checkin'];
        $entity = (new Entity())->setTable('statuses');
        foreach ($statuses as $status) {
            Record::create(['title' => $status], $entity);
        }

        $user = (new User(['email' => $this->argument('email')]))->setDefaults()->setAndSave(['status_id' => 1]);

        $this->output('Godfather #' . $user->id . ' created');
    }

}