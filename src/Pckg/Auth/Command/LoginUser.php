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

            $hashedPassword = $user->password;
            if (!$auth->hashedPasswordMatches($hashedPassword, $password)) {
                continue;
            }

            if ($auth->performLogin($user)) {
                /**
                 * @T00D00 - login user on all providers!
                 */
                $auth->useProvider($provider);
                trigger('user.loggedIn', [$auth->getUser()]);
                if ($autologin) {
                    $auth->setAutologin();
                }

                return $this->successful();
            }
        }

        return $this->error();
    }

}