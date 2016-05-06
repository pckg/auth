<?php

namespace Pckg\Auth\Command;


use Pckg\Concept\Command\Stated;
use Pckg\Concept\Event\Dispatcher;
use Pckg\Framework\Request;
use Pckg\Auth\Entity\Users;
use Pckg\Auth\Form\Login;
use Pckg\Auth\Service\Auth;

/**
 * Class LoginUser
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
     * @var Users
     */
    protected $users;

    /**
     * @param Request $request
     * @param Auth    $authHelper
     * @param Users   $eUsers
     */
    public function __construct(Auth $auth, Users $users, Login $loginForm)
    {
        $this->auth = $auth;
        $this->users = $users;
        $this->loginForm = $loginForm;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $data = $this->loginForm->getRawData(['email', 'password']);

        $rUser = $this->users
            ->where('email', $data['email'])
            ->where('password', $this->auth->makePassword($data['password']))
            ->one();

        if ($rUser && $this->auth->performLogin($rUser)) {
            trigger('user.loggedIn', [$rUser]);

            return $this->successful();
        }

        return $this->error();
    }

}