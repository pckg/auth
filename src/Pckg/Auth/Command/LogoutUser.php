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
     * @return mixed
     */
    public function execute()
    {
        auth()->logout();

        return $this->successful();
    }

}