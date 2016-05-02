<?php

namespace Pckg\Auth\Service;

interface ProviderInterface
{

    public function getUser();

    public function redirectToLogin();

    public function logout();

}