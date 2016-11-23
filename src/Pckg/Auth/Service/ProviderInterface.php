<?php

namespace Pckg\Auth\Service;

interface ProviderInterface
{

    public function redirectToLogin();

    public function logout();

}