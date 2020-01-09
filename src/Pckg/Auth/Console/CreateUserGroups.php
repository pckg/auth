<?php namespace Pckg\Auth\Console;

use Exception;
use Pckg\Auth\Entity\Users;
use Pckg\Auth\Record\User;
use Pckg\Auth\Record\UserGroup;
use Pckg\Framework\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class CreateUserGroups extends Command
{

    protected function configure()
    {
        $this->setName('auth:create-user-groups')->setDescription('Create user groups');
    }

    public function handle()
    {
        $this->outputDated('Updating');
        $statuses = [
            1 => 'Super admin',
            2 => 'User',
            3 => 'Administrator',
            4 => 'PR',
            5 => 'Checkin',
            6 => 'Cashier',
            7 => 'Analyst',
        ];
        foreach ($statuses as $id => $status) {
            UserGroup::getAndUpdateOrCreate(['id' => $id], ['id' => $id, 'title' => $status]);
        }
        $this->outputDated('Updated');
    }

}