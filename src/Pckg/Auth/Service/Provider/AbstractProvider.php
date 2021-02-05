<?php

namespace Pckg\Auth\Service\Provider;

use Pckg\Auth\Service\ProviderInterface;

/**
 * Class AbstractProvider
 *
 * @package Pckg\Auth\Service\Provider
 */
abstract class AbstractProvider implements ProviderInterface
{

    /**
     * @var
     */
    protected $entity;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var
     */
    protected $identifier;

    /**
     * @param $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        $class = $this->entity;

        return (new $class())->nonDeleted();
    }

    /**
     * @param $config
     */
    public function applyConfig($config)
    {
        $this->setEntity($config['entity']);
        $this->config = $config;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param  $email
     * @param  $password
     * @return mixed
     */
    public function getUserByEmailAndPassword($email, $password)
    {
        return $this->getEntity()->where('email', $email)->where('password', $password)->one();
    }

    /**
     * @param  $email
     * @return mixed
     */
    public function getUserByEmail($email)
    {
        return $this->getEntity()->where('email', $email)->one();
    }

    /**
     * @param  $id
     * @return mixed
     */
    public function getUserById($id)
    {
        return $this->getEntity()->where('id', $id)->one();
    }
}
