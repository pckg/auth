<?php

namespace Pckg\Auth\Controller;

use Pckg\Auth\Factory\Auth as AuthFactory;
use Pckg\Auth\Service\Auth;
use Pckg\Framework\Response;
use Pckg\Framework\Router;

/**
 * Class Facebook
 *
 * @package Pckg\Auth\Controller
 */
class Facebook extends Auth
{

    function getLoginAction()
    {
        auth()->useProvider(AuthFactory::getFacebookProvider())
            ->getProvider()
            ->redirectToLogin();
    }

    /**
     * @param  Response $response
     * @param  Router   $router
     * @throws \Exception
     */
    function getTakeloginAction(Response $response, Router $router)
    {
        $success = auth()->useProvider(AuthFactory::getFacebookProvider())
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