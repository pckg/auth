<?php

namespace Pckg\Auth\Entity\Adapter;

use Pckg\Auth\Service\Auth as AuthService;
use Pckg\Database\Entity\Extension\Adapter\AuthInterface;

/**
 * Class Auth
 *
 * @package Pckg\Auth\Entity\Adapter
 */
class Auth implements AuthInterface
{

    /**
     * @var AuthService
     */
    protected $authService;

    public function __construct(AuthService $auth)
    {
        $this->authService = $auth;
    }

    /**
     * @return int|mixed|string|null
     */
    public function groupId()
    {
        return $this->authService->getGroupId();
    }

    /**
     * @return int|string|null
     */
    public function userId()
    {
        return $this->authService->getUserId();
    }
}
