<?php

namespace Pckg\Auth\Command;

use Pckg\Concept\Command\Stated;
use Pckg\Concept\Reflect;

/**
 * Class LoginUser
 *
 * @package Pckg\Auth\Command
 */
class LoginUser
{

    use Stated;

    /**
     * @return mixed
     */
    public function execute()
    {
    }

    /**
     * @param  $email
     * @param  $password
     * @param  false $autologin
     * @return mixed|null
     * @throws \Exception
     */
    public function executeManual($email, $password, $autologin = false)
    {
        foreach (config('pckg.auth.providers') as $providerKey => $providerConfig) {
            $auth = auth($providerKey);
            $provider = $auth->getProvider();

            /**
             * If user doesnt exists, don't proceed with execution.
             */
            if (!($user = $provider->getUserByEmail($email))
            ) {
                continue;
            }

            /**
             * If password is not set.
             */
            if (!$user->password) {
                $this->error(
                    [
                        'type' => 'activateAccount',
                    ]
                );
                continue;
            }

            /**
             * If password is incorrect ...
             */
            $hashedPassword = $user->password;
            if (!$auth->hashedPasswordMatches($hashedPassword, $password)) {
                continue;
            }

            if ($auth->performLogin($user)) {
                /**
                 * @T00D00 - login user on all providers!
                 */
                $auth->useProvider($provider);
                if ($autologin) {
                    $auth->setAutologin();
                }

                return $this->successful();
            }
        }

        return $this->error();
    }

}