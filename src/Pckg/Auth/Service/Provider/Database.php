<?php namespace Pckg\Auth\Service\Provider;

use Pckg\Auth\Service\Auth;

class Database extends AbstractProvider
{

    /**
     * @var Auth
     */
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function redirectToLogin()
    {
        redirect(url('login'));
    }

    public function logout()
    {
    }

}