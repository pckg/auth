<?php

namespace Pckg\Auth\Command;

use Defuse\Crypto\Key;
use Pckg\Auth\Entity\Users;
use Pckg\Auth\Service\Auth;
use Pckg\Concept\Command\Stated;
use Pckg\Mail\Service\Mail\Adapter\User;
use Pckg\Mail\Service\Mail\Handler\Command;

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
            if (
                !($user = $provider->getUserByEmail($email))
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

            /**
             * This is all about 2FA?
             */
            if ($user->has2FA) {
                $this->twoFA($user);
                return;
            }

            /**
             * @T00D00 - login user on all providers!
             */
            if ($auth->performLogin($user)) {
                $provided = $auth->useProvider($provider);
                if (!$provided) {
                    return $this->error();
                }
                if ($autologin) {
                    $auth->setAutologin();
                }

                return $this->successful();
            }
        }

        return $this->error();
    }

    /**
     * @param $user
     * @return \Pckg\Framework\Response
     */
    public function twoFA($user)
    {
        /**
         * We need to create a 2FA code and send it.
         * The session must be the same, and refreshed.
         * @var Command $mailHandler
         */
        auth()->regenerateSession();
        $key = Key::createNewRandomKey()->saveToAsciiSafeString();
        $code = auth()->createPassword(6, Auth::GEN_NUM);
        $signatureValue = json_encode([
            'key' => $key,
            'user' => $user->id,
            'session' => session_id(), // raw? it is public info, so okay
            'code' => (string)$code, // will be entered by user
        ]);
        $signature = \auth()->hashPassword($signatureValue);

        /**
         * When the 2FA form is submitted, the info MUST match.
         */
        $_SESSION['twoFAkey'] = $key;
        $_SESSION['twoFAsignature'] = $signatureValue;

        /**
         *
         */
        $mailData = [
            'fetch' => [Users::class => $user->id],
            'data' => ['code' => $code]
        ];
        $mailHandler = resolve(Command::class);
        $mailHandler->send('twoFA:login', new User($user), $mailData);

        return response()->respond([
            'success' => true,
            'twoFAmethod' => 'email',
            'twoFA' => 'sc*********et',
        ]);
    }
}
