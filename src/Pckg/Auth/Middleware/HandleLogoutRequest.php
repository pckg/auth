<?php

namespace Pckg\Auth\Middleware;

use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Framework\Request;
use Pckg\Framework\Response;
use Pckg\Auth\Service\Auth;

class HandleLogoutRequest extends AbstractChainOfReponsibility
{

    protected $request;

    protected $auth;

    protected $users;

    protected $dispatcher;

    public function __construct(Request $request, Auth $auth, Response $response)
    {
        $this->request = $request;
        $this->auth = $auth;
        $this->response = $response;
    }

    public function execute(callable $next)
    {
        if ($this->request->isGet() && $this->request->get('logout')) {
            $this->auth->logout();
            $this->response->redirect('/');
        }

        return $next();
    }

}