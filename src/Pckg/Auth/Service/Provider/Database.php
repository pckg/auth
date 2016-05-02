<?php

namespace Pckg\Auth\Service\Provider;

use Pckg\Framework\Response;
use Pckg\Framework\Router;
use Pckg\Auth\Entity\Users;
use Pckg\Auth\Service\Auth;
use Pckg\Auth\Service\ProviderInterface;

class Database implements ProviderInterface
{

    protected $users;

    public function __construct(Users $users, Response $response, Router $router, Auth $auth)
    {
        $this->users = $users;
        $this->response = $response;
        $this->router = $router;
        $this->auth = $auth;
    }

    public function getUser()
    {

    }

    public function redirectToLogin()
    {
        $this->response->redirect($this->router->make('login'));
    }

    public function logout()
    {

    }


}