<?php namespace Pckg\Auth\Service\Provider;

use Pckg\Auth\Service\ProviderInterface;

abstract class AbstractProvider implements ProviderInterface
{

    protected $entity;

    protected $config = [];

    protected $identifier;

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    public function getEntity()
    {
        $class = $this->entity;

        return (new $class)->nonDeleted();
    }

    public function applyConfig($config)
    {
        $this->setEntity($config['entity']);
        $this->config = $config;
    }

    public function setIdentifier(string $identifier)
    {
        $this->identifier = $identifier;
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