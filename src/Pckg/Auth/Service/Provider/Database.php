<?php

namespace Pckg\Auth\Service\Provider;

use Pckg\Auth\Entity\Users;
use Pckg\Auth\Service\Auth;
use Pckg\Auth\Service\ProviderInterface;
use Pckg\Framework\Response;
use Pckg\Framework\Router;

class Database implements ProviderInterface
{

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Auth
     */
    protected $auth;

    public function __construct(Response $response, Router $router, Auth $auth) {
        $this->response = $response;
        $this->router = $router;
        $this->auth = $auth;
    }

    public function getUserByEmailAndPassword($email, $password) {
        return (new Users())->where('email', $email)->where('password', $password)->one();
    }

    public function getUserByAutologin($autologin) {
        return (new Users())->where('id', $autologin)->one();
    }

    public function getUser() {
        return (new Users())->where('id', $_SESSION['Auth']['user_id'] ?? null)->one();
    }

    public function redirectToLogin() {
        $this->response->redirect($this->router->make('login'));
    }

    public function logout() {

    }

}