<?php

namespace Pckg\Auth\Command;

use Pckg\Auth\Record\User;
use Pckg\Concept\Command\Stated;
use Pckg\Concept\CommandInterface;
use Pckg\Framework\Request;

/**
 * Class SendNewPassword
 *
 * @package Pckg\Auth\Command
 */
class SendNewPassword
{

    use Stated;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var User
     */
    protected $rUser;

    /**
     * @param Request $request
     * @param User    $rUser
     */
    public function __construct(Request $request, User $rUser)
    {
        $this->request = $request;
        $this->rUser = $rUser;
    }

    /**
     * @return mixed
     */
    public function execute()
    {

        return $this->error();
    }

}