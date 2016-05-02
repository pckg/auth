<?php

namespace Pckg\Auth\Middleware;

use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Framework\Request;
use Pckg\Framework\Response;
use Pckg\Framework\Router;
use Pckg\Auth\Service\Auth;

class RestrictAccess extends AbstractChainOfReponsibility
{

    public function __construct(Request $request, Auth $auth, Router $router, Response $response)
    {
        $this->request = $request;
        $this->auth = $auth;
        $this->router = $router;
        $this->response = $response;
    }

    public function execute(callable $next)
    {
        if (!$this->auth->isLoggedIn() && $this->router->get('name') != 'login') {
            //$this->response->redirect($this->router->make('login'));

        } else if ($this->auth->isLoggedIn() && $this->router->get('name') == 'login') {
            //$this->response->redirect('/');

        }

        return $next();
    }

}