<?php namespace Pckg\Auth\Console;

use Exception;
use Pckg\Auth\Entity\Users;
use Pckg\Auth\Record\User;
use Pckg\Auth\Record\UserGroup;
use Pckg\Framework\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class CreateUserGroups
 *
 * @package Pckg\Auth\Console
 */
class CreateUserGroups extends Command
{

    protected function configure()
    {
        $this->setName('auth:create-user-groups')->setDescription('Create user groups');
    }

    public function handle()
    {
        $this->outputDated('Updating');
        $statuses = config('pckg.auth.groups', []);
        foreach ($statuses as $id => $data) {
            UserGroup::getAndUpdateOrCreate(['id' => $id], $data);
        }
        $this->outputDated('Updated');
    }

}