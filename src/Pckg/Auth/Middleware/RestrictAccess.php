<?php

namespace Pckg\Auth\Middleware;

use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Framework\Response;
use Pckg\Framework\Router;
use Pckg\Auth\Service\Auth;

class RestrictAccess extends AbstractChainOfReponsibility
{

    public function __construct(Auth $auth, Router $router, Response $response)
    {
        $this->auth = $auth;
        $this->router = $router;
        $this->response = $response;
    }

    public function execute(callable $next)
    {
        if (!$this->auth->isLoggedIn() && !in_array($this->router->get('name'), ['login', 'impero.git.webhook', 'derive.orders.voucher.preview'])) {
            $this->response->redirect(url('login'));

        } else if ($this->auth->isLoggedIn() && $this->router->get('name') == 'login') {
            $this->response->redirect('/');

        }

        return $next();
    }

}