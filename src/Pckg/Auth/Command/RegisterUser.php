<?php

namespace Pckg\Auth\Command;

use Pckg\Auth\Record\User;
use Pckg\Concept\Command\Stated;
use Pckg\Concept\CommandInterface;
use Pckg\Framework\Request;

/**
 * Class RegisterUser
 *
 * @package Pckg\Auth\Command
 */
class RegisterUser
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
        $this->rUser->set($this->request->post());

        if ($this->rUser->password) {
            $this->rUser->password = auth()->hashPassword($this->rUser->password);
        }

        if ($this->rUser->save()) {
            // trigger('user.registered', [$this->rUser]);

            return $this->successful();
        }

        return $this->error();
    }
}
