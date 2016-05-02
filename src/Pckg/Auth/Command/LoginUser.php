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
    protected $eUsers;

    /**
     * @param Request $request
     * @param Auth $authHelper
     * @param Users $eUsers
     */
    public function __construct(Auth $authHelper, Users $eUsers, Login $loginForm, Dispatcher $dispatcher)
    {
        $this->authHelper = $authHelper;
        $this->eUsers = $eUsers;
        $this->loginForm = $loginForm;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $this->loginForm->initFields();

        //if ($this->loginForm->isValid()) {
        $data = $this->loginForm->getRawData(['email', 'password']);

        $rUser = $this->eUsers
            ->where('email', $data['email'])
            ->where('password', $this->authHelper->makePassword($data['password']))
            ->one();

        if ($rUser && $rUser->isActivated() && $this->authHelper->performLogin($rUser)) {
            $this->dispatcher->trigger('user.loggedIn', [$rUser]);

            return $this->successful();
        }
        //}

        return $this->error();
    }

}