<?php namespace Pckg\Auth\Service\Provider;

use Derive\Orders\Entity\Users;
use Pckg\Auth\Service\ProviderInterface;

abstract class AbstractProvider implements ProviderInterface
{

    protected $entity = Users::class;

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    public function getEntity()
    {
        $class = $this->entity;

        return (new $class)->nonDeleted();
    }

    public function getUserByEmailAndPassword($email, $password)
    {
        return $this->getEntity()->where('email', $email)->where('password', $password)->one();
    }

    public function getUserByEmail($email)
    {
        return $this->getEntity()->where('email', $email)->one();
    }

    public function getUserById($id)
    {
        return $this->getEntity()->where('id', $id)->one();
    }

}