<?php

namespace Pckg\Auth\Controller;

use Pckg\Auth\Command\LoginUser;
use Pckg\Auth\Command\LogoutUser;
use Pckg\Auth\Command\RegisterUser;
use Pckg\Auth\Command\SendNewPassword;
use Pckg\Auth\Entity\Users;
use Pckg\Auth\Form\ForgotPassword;
use Pckg\Auth\Form\Login;
use Pckg\Auth\Form\Register;
use Pckg\Auth\Service\Auth as AuthService;
use Pckg\Concept\Event\Dispatcher;
use Pckg\Framework\Controller;
use Pckg\Framework\Exception\NotFound;
use Pckg\Framework\Response;
use Pckg\Framework\Router;

/**
 * Class Auth
 *
 * @package Pckg\Auth\Controller
 */
class Auth extends Controller
{

    function getLoginAction(Login $loginForm)
    {
        return view(
            'login',
            [
                'form' => $loginForm,
            ]
        );
    }

    function postLoginAction(LoginUser $loginUserCommand)
    {
        $loginUserCommand->onSuccess(
            function() {
                $this->response()->respondWithSuccessRedirect($this->auth()->getUser()->getDashboardUrl());
            }
        )->onError(
            function() {
                $this->response()->respondWithError(
                    [
                        'text' => __('pckg.auth.error'),
                    ]
                );

            }
        )->execute();
    }

    function getLogoutAction(LogoutUser $logoutUserCommand, Response $response)
    {
        $logoutUserCommand->onSuccess(
            function() use ($response) {
                $response->redirect('/');

            }
        )->onError(
            function() use ($response) {
                $response->redirect('/');

            }
        )->execute();
    }

    function getRegisterAction(Register $registerForm, AuthService $authHelper, Response $response)
    {
        if ($authHelper->isLoggedIn()) {
            $response->redirect('/');
        }

        return view(
            "vendor/lfw/auth/src/Pckg/Auth/View/register",
            [
                'form' => $registerForm->initFields(),
            ]
        );
    }

    function postRegisterAction(RegisterUser $registerUserCommand, Dispatcher $dispatcher, Response $response)
    {
        $registerUserCommand->onSuccess(
            function() use ($response) {
                $response->redirect('/auth/registered?successful');

            }
        )->onError(
            function() use ($response) {
                $response->redirect('/auth/register?error');

            }
        )->execute();
    }

    function getActivateAction(ActivateUser $activateUserCommand, Router $router, Users $eUsers, Response $response)
    {
        $rUser = $eUsers->where(
            'activation',
            $router->get('activation')
        )->oneOrFail(new NotFound('User not found. Maybe it was already activated?'));

        return $activateUserCommand->setUser($rUser)
                                   ->onSuccess(
                                       function() use ($response) {
                                           $response->redirect('/auth/activated?succesful');

                                       }
                                   )
                                   ->onError(
                                       function() {
                                           return view('vendor/lfw/auth/src/Pckg/Auth/View/activationFailed');

                                       }
                                   )->execute();
    }

    function getForgotPasswordAction(ForgotPassword $forgotPasswordForm)
    {
        return view(
            "vendor/lfw/auth/src/Pckg/Auth/View/forgotPassword",
            [
                'form' => $forgotPasswordForm->initFields(),
            ]
        );
    }

    function postForgotPasswordAction(SendNewPassword $sendNewPasswordCommand)
    {
        $sendNewPasswordCommand->onSuccess(
            function() {
                $this->response()->respondWithSuccess(['text' => 'Password was sent']);
            }
        )->onError(
            function() {
                $this->response()->respondWithError(['text' => 'Email not found ...']);

            }
        )->execute();
    }

    function getForgotPasswordSuccessAction()
    {
        return view("vendor/lfw/auth/src/Pckg/Auth/View/forgotPasswordSuccess");
    }

    function getForgotPasswordErrorAction()
    {
        return view("vendor/lfw/auth/src/Pckg/Auth/View/forgotPasswordError");
    }

    public function getUserHeaderAction()
    {
        return view('Pckg\Auth:userHeader');
    }

}
