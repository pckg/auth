<?php

namespace Pckg\Auth\Entity\Adapter;

use Pckg\Database\Entity\Extension\Adapter\Auth as AuthInterface;
use Pckg\Auth\Service\Auth as AuthService;

class Auth implements AuthInterface
{

    protected $authService;

    public function __construct(AuthService $auth)
    {
        $this->authService = $auth;
    }

    public function groupId()
    {
        return $this->authService->getGroupId();
    }

    public function userId()
    {
        return $this->authService->getUserId();
    }
}