<?php namespace Pckg\Auth\Console;

use Exception;
use Pckg\Auth\Entity\Users;
use Pckg\Auth\Record\User;
use Pckg\Auth\Record\UserGroup;
use Pckg\Framework\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class CreateUser
 * @package Pckg\Auth\Console
 */
class CreateUser extends Command
{

    protected function configure()
    {
        $this->setName('auth:create-user')->setDescription('Create user');
    }

    public function handle()
    {
        /**
         * Ask for email
         */
        $email = $this->askQuestion('Enter email:');

        /**
         * Require email to be valid.
         */
        if (!isValidEmail($email)) {
            throw new Exception('Invalid email provided');
        }

        /**
         * Check for unique user.
         */
        if (User::gets(['email' => $email])) {
            throw new Exception('User already exists');
        }

        /**
         * Get password.
         */
        $password = $this->askQuestion('Enter password');

        /**
         * Throw exception when not set.
         */
        if (!$password) {
            throw new Exception('No password set');
        }

        /**
         * Hash password.
         */
        $password = auth()->hashPassword($password);

        $user = (new User(
            [
                'email' => $email,
                'password' => $password,
                'autologin' => sha1(sha1($email) . sha1(config('identifier', null))),
            ]
        ))->setDefaults()->setAndSave(['user_group_id' => 2]);

        $this->output('User #' . $user->id . ' created');
    }

}