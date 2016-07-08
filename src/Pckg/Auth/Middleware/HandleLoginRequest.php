<?php

namespace Pckg\Auth\Middleware;

use Pckg\Auth\Entity\Users;
use Pckg\Auth\Service\Auth;
use Pckg\Concept\Event\Dispatcher;
use Pckg\Framework\Request;
use Pckg\Framework\Response;

class HandleLoginRequest
{

    protected $request;

    protected $auth;

    protected $users;

    protected $dispatcher;

    public function __construct(Request $request, Auth $auth, Users $users, Dispatcher $dispatcher, Response $response)
    {
        $this->request = $request;
        $this->auth = $auth;
        $this->users = $users;
        $this->dispatcher = $dispatcher;
        $this->response = $response;
    }

    public function execute(callable $next)
    {
        if ($this->request->post->has(['email', 'password'])) {
            $rUser = $this->users->getUserByEmailAndPassword(
                $this->request->post->email,
                $this->request->post->password
            );

            if ($rUser && $rUser->isActivated() && $this->auth->performLogin($rUser)) {
                $this->dispatcher->trigger('user.loggedIn', [$rUser]);
                $this->response->redirect('/?login_successful');
            }
        }

        return $next();
    }

}