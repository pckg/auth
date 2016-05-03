<?php

namespace Pckg\Auth\Controller;

use Pckg\Concept\Event\Dispatcher;

use Pckg\Framework\Request\Data\Session;
use Pckg\Framework\Response;
use Pckg\Framework\Router;
use Pckg\Auth\Command\LoginUser;
use Pckg\Auth\Command\LogoutUser;
use Pckg\Auth\Command\RegisterUser;
use Pckg\Auth\Command\SendNewPassword;
use Pckg\Auth\Entity\Users;
use Pckg\Auth\Event\UserLoggedIn;
use Pckg\Auth\Form\ForgotPassword;
use Pckg\Auth\Form\Login;
use Pckg\Auth\Form\Register;
use Pckg\Auth\Service\Auth as AuthService;

/**
 * Class Auth
 * @package Pckg\Auth\Controller
 */
class Auth
{

    protected $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        $dispatcher->registerEvent(new UserLoggedIn());
    }

    public function events()
    {
        return [
            new UserLoggedIn(),
        ];
    }

    public function getLoginStatusAction(AuthService $authService, Session $session)
    {
        return view('loginStatus', [
            'auth'    => $authService,
            'session' => $session,
        ]);
    }

    function getLoginAction(Login $loginForm)
    {
        return view('login', [
            'form' => $loginForm->initFields(),
        ]);
    }

    function postLoginAction(LoginUser $loginUserCommand, Response $response)
    {
        $loginUserCommand->onSuccess(function () use ($response) {
            $response->redirect('/?success');

        })->onError(function () use ($response) {
            $response->redirect('/login?error');

        })->execute();
    }

    function getLogoutAction(LogoutUser $logoutUserCommand, Response $response)
    {
        $logoutUserCommand->onSuccess(function () use ($response) {
            $response->redirect('/');

        })->onError(function () use ($response) {
            $response->redirect('/');

        })->execute();
    }

    function getRegisterAction(Register $registerForm, AuthService $authHelper, Response $response)
    {
        if ($authHelper->isLoggedIn()) {
            $response->redirect('/');
        }

        return view("vendor/lfw/auth/src/Pckg/Auth/View/register", [
            'form' => $registerForm->initFields(),
        ]);
    }

    function postRegisterAction(RegisterUser $registerUserCommand, Dispatcher $dispatcher, Response $response)
    {
        $registerUserCommand->onSuccess(function () use ($response) {
            $response->redirect('/auth/registered?successful');

        })->onError(function () use ($response) {
            $response->redirect('/auth/register?error');

        })->execute();
    }

    function getActivateAction(ActivateUser $activateUserCommand, Router $router, Users $eUsers, Response $response)
    {
        $rUser = $eUsers->where('activation', $router->get('activation'))->oneOrFail(new \Exception\NotFound('User not found. Maybe it was already activated?'));

        return $activateUserCommand->setUser($rUser)
            ->onSuccess(function () use ($response) {
                $response->redirect('/auth/activated?succesful');

            })
            ->onError(function () {
                return view('vendor/lfw/auth/src/Pckg/Auth/View/activationFailed');

            })->execute();
    }

    function getForgotPasswordAction(ForgotPassword $forgotPasswordForm)
    {
        return view("vendor/lfw/auth/src/Pckg/Auth/View/forgotPassword", [
            'form' => $forgotPasswordForm->initFields(),
        ]);
    }

    function postForgotPasswordAction(SendNewPassword $sendNewPasswordCommand, Response $response)
    {
        $sendNewPasswordCommand->onSuccess(function () use ($response) {
            $response->redirect('/auth/forgot-password/success');

        })->onError(function () use ($response) {
            $response->redirect('/auth/forgot-password/error');

        })->execute();
    }

    function getForgotPasswordSuccessAction()
    {
        return view("vendor/lfw/auth/src/Pckg/Auth/View/forgotPasswordSuccess");
    }

    function getForgotPasswordErrorAction()
    {
        return view("vendor/lfw/auth/src/Pckg/Auth/View/forgotPasswordError");
    }
}
