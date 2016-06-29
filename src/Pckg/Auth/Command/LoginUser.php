<?php

namespace Pckg\Auth\Command;

use Pckg\Auth\Form\Login;
use Pckg\Auth\Service\Auth;
use Pckg\Concept\Command\Stated;
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
    public function __construct(Auth $auth, Login $loginForm) {
        $this->auth = $auth;
        $this->loginForm = $loginForm;
    }

    /**
     * @return mixed
     */
    public function execute() {
        $data = $this->loginForm->getRawData(['email', 'password']);

        if ($this->auth->login($data['email'], $data['password'])) {
            trigger('user.loggedIn', [$this->auth->getUser()]);
            if (isset($data['autologin'])) {
                $this->auth->setAutologin();
            }

            return $this->successful();
        }

        return $this->error();
    }

}