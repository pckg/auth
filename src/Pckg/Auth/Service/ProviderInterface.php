<?php

namespace Pckg\Auth\Service;

/**
 * Interface ProviderInterface
 *
 * @package Pckg\Auth\Service
 */
interface ProviderInterface
{

    public function redirectToLogin();

    public function logout();
}
