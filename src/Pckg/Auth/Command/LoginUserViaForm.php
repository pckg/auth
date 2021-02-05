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
class LoginUserViaForm extends LoginUser
{

    /**
     * @var Login
     */
    protected $loginForm;

    /**
     * @param Request $request
     * @param Auth    $auth
     */
    public function __construct(Login $loginForm)
    {
        $this->loginForm = $loginForm;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $data = $this->loginForm->getRawData(['email', 'password']);

        $this->executeManual($data['email'], $data['password'], true);
    }

}