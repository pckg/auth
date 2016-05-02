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

    /*
     * Redirects already loggedin users to /.
     * Shows login form.
     */
    /**
     * @param Login       $loginForm
     * @param Response    $response
     * @param AuthService $authHelper
     *
     * @return \LFW\View\Twig
     */
    function getLoginAction(Login $loginForm)
    {
        return view('login', [
            'form' => $loginForm->initFields(),
        ]);
    }

    /*
     * Handles login form submission.
     * Redirects to /?success on success login
     * Redirects to /login?error on error
     */
    /**
     * @param LoginUser $loginUserCommand
     * @param Response  $response
     */
    function postLoginAction(LoginUser $loginUserCommand, Response $response)
    {
        $loginUserCommand->onSuccess(function () use ($response) {
            $response->redirect('/?success');

        })->onError(function () use ($response) {
            $response->redirect('/login?error');

        })->execute();
    }

    /*
     * Redirects user to / on successuful logout
     * Recirects user to / on error
     */
    /**
     * @param LogoutUser $logoutUserCommand
     * @param Response   $response
     */
    function getLogoutAction(LogoutUser $logoutUserCommand, Response $response)
    {
        $logoutUserCommand->onSuccess(function () use ($response) {
            $response->redirect('/');

        })->onError(function () use ($response) {
            $response->redirect('/');

        })->execute();
    }

    /*
     * Redirects already loggedin users to /.
     * Shows register form.
     */
    /**
     * @param Register        $registerForm
     * @param LFW\Helper\Auth $authHelper
     *
     * @return \LFW\View\Twig
     */
    function getRegisterAction(Register $registerForm, AuthService $authHelper, Response $response)
    {
        if ($authHelper->isLoggedIn()) {
            $response->redirect('/');
        }

        return view("vendor/lfw/auth/src/Pckg/Auth/View/register", [
            'form' => $registerForm->initFields(),
        ]);
    }

    /*
     * Handles register form submission.
     * Redirects user to /auth/registered?successful on successful registration.
     * Redirects user to /auth/register?error on error.
     */
    /**
     * @param RegisterUser $registerUserCommand
     * @param Dispatcher   $dispatcher
     * @param Response     $response
     */
    function postRegisterAction(RegisterUser $registerUserCommand, Dispatcher $dispatcher, Response $response)
    {
        $registerUserCommand->onSuccess(function () use ($response) {
            $response->redirect('/auth/registered?successful');

        })->onError(function () use ($response) {
            $response->redirect('/auth/register?error');

        })->execute();
    }

    /*
     * Handles user activation.
     * Redirects user to /auth/activated?successful on successful activation
     * Shows error view on error
     */
    /**
     * @param ActivateUser $activateUserCommand
     * @param Router       $router
     * @param Users        $eUsers
     * @param Response     $response
     *
     * @return mixed
     * @throws \Exception
     */
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

    /*
     * Show forgotten password form.
     *
     * @param ForgotPassword $forgotPasswordForm
     * @return View
     */
    /**
     * @param ForgotPassword $forgotPasswordForm
     *
     * @return \LFW\View\Twig
     */
    function getForgotPasswordAction(ForgotPassword $forgotPasswordForm)
    {
        return view("vendor/lfw/auth/src/Pckg/Auth/View/forgotPassword", [
            'form' => $forgotPasswordForm->initFields(),
        ]);
    }

    /*
     * Handles forgotten form submission.
     * Redirects user to /auth/new-password-sent on success.
     * Shows forgot password failed view on error.
     */
    /**
     * @param SendNewPassword $sendNewPasswordCommand
     * @param Response        $response
     */
    function postForgotPasswordAction(SendNewPassword $sendNewPasswordCommand, Response $response)
    {
        $sendNewPasswordCommand->onSuccess(function () use ($response) {
            $response->redirect('/auth/forgot-password/success');

        })->onError(function () use ($response) {
            $response->redirect('/auth/forgot-password/error');

        })->execute();
    }

    /*
     * Show successful notice
     *
     * @param ForgotPassword $forgotPasswordForm
     * @return View
     */
    /**
     * @return \LFW\View\Twig
     */
    function getForgotPasswordSuccessAction()
    {
        return view("vendor/lfw/auth/src/Pckg/Auth/View/forgotPasswordSuccess");
    }

    /*
     * Show error notice
     *
     * @param ForgotPassword $forgotPasswordForm
     * @return View
     */
    /**
     * @return \LFW\View\Twig
     */
    function getForgotPasswordErrorAction()
    {
        return view("vendor/lfw/auth/src/Pckg/Auth/View/forgotPasswordError");
    }
}
