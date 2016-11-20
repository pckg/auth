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
     * @var Auth
     */
    protected $auth;

    protected $entity = Users::class;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    public function getEntity()
    {
        $class = $this->entity;

        return new $class;
    }

    public function getUserByEmailAndPassword($email, $password)
    {
        return $this->getEntity()->where('email', $email)->where('password', $password)->one();
    }

    public function getUserByEmail($email)
    {
        return $this->getEntity()->where('email', $email)->one();
    }

    public function getUserByAutologin($autologin)
    {
        return $this->getEntity()->where('id', $autologin)->one();
    }

    public function getUser()
    {
        return $this->getEntity()->where('id', $_SESSION['Auth']['user_id'] ?? null)->one();
    }

    public function redirectToLogin()
    {
        redirect(url('login'));
    }

    public function logout()
    {

    }

}