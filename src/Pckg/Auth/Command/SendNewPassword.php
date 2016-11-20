<?php

namespace Pckg\Auth\Command;

use Derive\User\Service\Mail\User;
use Pckg\Concept\Command\Stated;
use Pckg\Concept\CommandInterface;
use Pckg\Concept\Reflect;

/**
 * Class SendNewPassword
 *
 * @package Pckg\Auth\Command
 */
class SendNewPassword
{

    use Stated;

    /**
     * @return mixed
     */
    public function execute()
    {
        $password = auth()->createPassword();

        foreach (config('pckg.auth.providers') as $providerKey => $providerConfig) {
            if (!$providerConfig['forgotPassword']) {
                continue;
            }

            /**
             * Create and set new provider.
             */
            $provider = Reflect::create($providerConfig['type'], [auth()]);
            $provider->setEntity($providerConfig['entity']);

            /**
             * If user doesnt exists, don't proceed with execution.
             */
            if (!($user = $provider->getUserByEmail(post('email')))) {
                continue;
            }

            $user->password = sha1($password . $providerConfig['hash']);
            $user->save();

            /**
             * Send email via queue.
             */
            email(
                'password-update',
                new User($user),
                [
                    'password' => $password,
                ],
                [
                    'user' => [
                        $user->getEntityClass() => $user->id,
                    ],
                ]
            );

            return $this->successful();
        }

        return $this->error();
    }

}