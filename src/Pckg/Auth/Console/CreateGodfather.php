<?php

namespace Pckg\Auth\Console;

use Exception;
use Pckg\Auth\Entity\Users;
use Pckg\Auth\Record\User;
use Pckg\Auth\Record\UserGroup;
use Pckg\Framework\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class CreateGodfather
 *
 * @package Pckg\Auth\Console
 */
class CreateGodfather extends Command
{
    protected function configure()
    {
        $this->setName('auth:create-godfather')->setDescription('Create godfather user')->addArguments(
            [
                'email' => 'Godfather email',
            ],
            InputArgument::REQUIRED
        )->addArguments(
            [
                'password' => 'Hashed godfather password',
            ],
            InputArgument::OPTIONAL
        );
    }

    public function handle()
    {
        $totalUsers = (new Users())->limit(1)->total();
        if ($totalUsers) {
            throw new Exception("There are already users in database");
        }

        (new CreateUserGroups())->executeManually();

        $password = $this->argument('password');

        /**
         * Ask for password if not set.
         */
        $manual = false;
        if (!$password) {
            $manual = true;
            $password = $this->askQuestion('Enter password:');
        }

        /**
         * Throw exception when not set.
         */
        if (!$password) {
            throw new Exception('No password set');
        }

        /**
         * Hash when manually entered.
         */
        if ($manual) {
            $password = auth()->hashPassword($password);
        }

        $user = (new User(
            [
                'email' => $this->argument('email'),
                'password' => $password,
                'autologin' => sha1(sha1($this->argument('email')) . sha1(config('identifier', null))),
            ]
        ))->setDefaults()->setAndSave(['user_group_id' => 1]);

        $this->output('Godfather #' . $user->id . ' created');
    }
}
