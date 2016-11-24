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

    /**
     * @var Auth
     */
    protected $authHelper;

    /**
     * @param Request $request
     * @param Auth    $auth
     */
    public function __construct(Auth $auth, Login $loginForm)
    {
        $this->auth = $auth;
        $this->loginForm = $loginForm;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $data = $this->loginForm->getRawData(['email', 'password']);

        foreach (config('pckg.auth.providers') as $providerKey => $providerConfig) {
            /**
             * Create and set new provider.
             */
            $provider = Reflect::create($providerConfig['type'], [$this->auth]);
            $provider->setEntity($providerConfig['entity']);
            /**
             * If user doesnt exists, don't proceed with execution.
             */
            if (!($user = $provider->getUserByEmailAndPassword(
                $data['email'],
                sha1($data['password'] . $providerConfig['hash'])
            ))
            ) {
                continue;
            }

            /**
             * Try to login.
             */
            $this->auth->useProvider($provider, $providerKey);
            if ($this->auth->performLogin($user)) {
                /**
                 * @T00D00 - login user on all providers!
                 */
                $this->auth->useProvider($provider);
                trigger('user.loggedIn', [$this->auth->getUser()]);
                if (isset($data['autologin'])) {
                    $this->auth->setAutologin();
                }

                return $this->successful();
            }
        }
        
        return $this->error();
    }

}