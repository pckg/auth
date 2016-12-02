<?php

namespace Pckg\Auth\Command;

use Pckg\Auth\Form\Login;
use Pckg\Auth\Service\Auth;
use Pckg\Concept\Command\Stated;
use Pckg\Concept\Reflect;
use Pckg\Framework\Request;

/**
 * Class LoginUser
 *
 * @package Pckg\Auth\Command
 */
class LoginUser
{

    use Stated;

    /**
     * @var Request
     */
    protected $request;

    protected $loginForm;

    /**
     * @param Request $request
     * @param Auth    $auth
     */
    public function __construct(Login $loginForm = null)
    {
        $this->loginForm = $loginForm;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        dd(db());
        $data = $this->loginForm->getRawData(['email', 'password']);

        $this->executeManual($data['email'], $data['password'], isset($data['autologin']));
    }

    public function executeManual($email, $password, $autologin = false)
    {
        foreach (config('pckg.auth.providers') as $providerKey => $providerConfig) {
            $auth = auth($providerKey);
            $provider = $auth->getProvider();

            /**
             * If user doesnt exists, don't proceed with execution.
             */
            if (!($user = $provider->getUserByEmailAndPassword(
                $email,
                sha1($password . $providerConfig['hash'])
            ))
            ) {
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