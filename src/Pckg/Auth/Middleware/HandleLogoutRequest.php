<?php

namespace Pckg\Auth\Middleware;

use Pckg\Auth\Service\Auth;
use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Framework\Request;
use Pckg\Framework\Response;

/**
 * Class HandleLogoutRequest
 * @package Pckg\Auth\Middleware
 */
class HandleLogoutRequest extends AbstractChainOfReponsibility
{

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Auth
     */
    protected $auth;

    /**
     * @var
     */
    protected $users;

    /**
     * @var
     */
    protected $dispatcher;

    public function __construct(Request $request, Auth $auth, Response $response)
    {
        $this->request = $request;
        $this->auth = $auth;
        $this->response = $response;
    }

    /**
     * @param callable $next
     * @return mixed
     */
    public function execute(callable $next)
    {
        if ($this->request->isGet() && $this->request->get('logout')) {
            $this->auth->logout();
            $this->response->redirect('/');
        }

        return $next();
    }

}