<?php

namespace Pckg\Auth\Middleware;

use Pckg\Auth\Service\Auth;
use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Framework\Request;
use Pckg\Framework\Response;

class LoginWithCookie extends AbstractChainOfReponsibility
{

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Auth
     */
    protected $auth;

    /**
     * @var Response
     */
    protected $response;

    public function __construct(Request $request, Auth $auth, Response $response)
    {
        $this->request = $request;
        $this->auth = $auth;
        $this->response = $response;
    }

    public function execute(callable $next)
    {
        if ($this->auth->isLoggedIn() || !$this->request->isGet() || !isset($_COOKIE['pckg.auth.autologin'])) {
            return $next();
        }

        $cookie = unserialize($_COOKIE['pckg.auth.autologin']);
        foreach ($cookie as $provider => $data) {
            $this->auth->useProvider($provider);
            $this->auth->autologin($data['user_id']);
        }

        return $next();
    }

}