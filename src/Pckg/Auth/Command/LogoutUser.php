<?php

namespace Pckg\Auth\Command;

use Pckg\Auth\Service\Auth;
use Pckg\Concept\Command\Stated;
use Pckg\Concept\CommandInterface;

/**
 * Class LogoutUser
 *
 * @package Pckg\Auth\Command
 */
class LogoutUser
{

    use Stated;

    /**
     * @var Auth
     */
    protected $authHelper;

    /**
     * @param Auth $authHelper
     */
    public function __construct(Auth $authHelper)
    {
        $this->authHelper = $authHelper;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $this->authHelper->logout();

        return $this->successful();
    }

}