<?php

namespace Pckg\Auth\Controller;

use Pckg\Auth\Service\Auth;
use Pckg\Concept\Event\Dispatcher;
use Pckg\Framework\Config;
use Pckg\Framework\Response;
use Pckg\Framework\Router;

class Facebook extends Auth
{

    function __construct(Dispatcher $dispatcher, Auth $auth, Config $config)
    {
        $this->facebook = new \Facebook\Facebook($config->get('defaults.auth.provider.facebook'));
        $this->auth = $auth;
    }

    function getLoginAction()
    {
        $this->auth
            ->useFacebookProvider($this->facebook)
            ->getProvider()
            ->redirectToLogin();
    }

    function getTakeloginAction(Response $response, Router $router)
    {
        $success = $this->auth
            ->useFacebookProvider($this->facebook)
            ->getProvider()
            ->handleTakelogin();

        $response->redirect(
            $success
                ? '/?success'
                : ($router->make('login') . '?error=fb')
        );
    }
}

?>