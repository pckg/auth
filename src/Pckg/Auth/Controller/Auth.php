<?php

namespace Pckg\Auth\Controller;

use Pckg\Auth\Command\LoginUserViaForm;
use Pckg\Auth\Command\LogoutUser;
use Pckg\Auth\Command\RegisterUser;
use Pckg\Auth\Command\SendPasswordCode;
use Pckg\Auth\Entity\Users;
use Pckg\Auth\Form\ForgotPassword;
use Pckg\Auth\Form\Login;
use Pckg\Auth\Form\PasswordCode;
use Pckg\Auth\Form\Register;
use Pckg\Auth\Form\ResetPassword;
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

    public function getUserAction()
    {
        $user = $this->auth()->getUser();

        return [
            'loggedIn' => $this->auth()->isLoggedIn(),
            'user'     => $user ? only($user->data(), ['id', 'email', 'name', 'surname', 'user_group_id']) : [],
        ];
    }

    public function getUserAddressesAction()
    {
        $user = $this->auth()->getUser();

        return [
            'addresses' => $user ? $user->addresses : [],
        ];
    }

    public function getLoginAction()
    {
        if (auth()->isLoggedIn()) {
            return $this->response()->redirect($this->auth()->getUser()->getDashboardUrl());
        }

        return '';
    }

    function postLoginAction(Login $loginForm, LoginUserViaForm $loginUserCommand)
    {
        /**
         * Form is valid, we need to check for password.
         */
        $loginUserCommand->onSuccess(function() {
            $user = $this->auth()->getUser();

            if ($user->isAdmin()) {
                trigger(Auth::class . '.adminLoggedIn', [$user]);
            }

            $this->response()->respondWithSuccessRedirect($user->getDashboardUrl());
        })->onError(function() {
            if ($this->request()->isAjax()) {
                $this->response()->respondWithError(['text' => __('pckg.auth.error')]);

                return;
            }

            $this->response()->respondWithErrorRedirect();
        })->execute();
    }

    function getLogoutAction(LogoutUser $logoutUserCommand, Response $response)
    {
        $logoutUserCommand->onSuccess(function() use ($response) {
            if ($this->request()->isJson()) {
                $response->respond([
                                       'success' => true,
                                   ]);
            } else {
                $response->redirect('/');
            }
        })->onError(function() use ($response) {
            if ($this->request()->isJson()) {
                $response->respond([
                                       'success' => false,
                                   ]);
            } else {
                $response->redirect('/');
            }
        })->execute();
    }

    function getRegisterAction(Register $registerForm, AuthService $authHelper, Response $response)
    {
        if ($authHelper->isLoggedIn()) {
            $response->redirect('/');
        }

        return view("vendor/lfw/auth/src/Pckg/Auth/View/register",
                    [
                        'form' => $registerForm->initFields(),
                    ]);
    }

    function postRegisterAction(RegisterUser $registerUserCommand, Dispatcher $dispatcher, Response $response)
    {
        $registerUserCommand->onSuccess(function() use ($response) {
            $response->redirect('/auth/registered?successful');
        })->onError(function() use ($response) {
            $response->redirect('/auth/register?error');
        })->execute();
    }

    function getActivateAction(ActivateUser $activateUserCommand, Router $router, Users $eUsers, Response $response)
    {
        $rUser = $eUsers->where('activation',
                                $router->get('activation'))
                        ->oneOrFail(new NotFound('User not found. Maybe it was already activated?'));

        return $activateUserCommand->setUser($rUser)->onSuccess(function() use ($response) {
            $response->redirect('/auth/activated?succesful');
        })->onError(function() {
            return view('vendor/lfw/auth/src/Pckg/Auth/View/activationFailed');
        })->execute();
    }

    function getForgotPasswordAction(ForgotPassword $forgotPasswordForm)
    {
        return view("vendor/lfw/auth/src/Pckg/Auth/View/forgotPassword",
                    [
                        'form' => $forgotPasswordForm->initFields(),
                    ]);
    }

    /**
     * Handle forgotten password request.
     * Generate new code valid for 24 hours.
     * Send email with details to user.
     *
     * @param ForgotPassword $forgotPasswordForm
     */
    public function postForgotPasswordAction(ForgotPassword $forgotPasswordForm)
    {
        /**
         * Receive email from sent data.
         */
        $data = $forgotPasswordForm->getData();

        /**
         * Fetch user.
         */
        $user = (new Users())->where('email', $data['email'])->oneOrFail();

        /**
         * Generate code and send email.
         */
        (new SendPasswordCode($user))->execute();

        /**
         * Return response.
         */
        return [
            'success' => true,
            'message' => 'Password reset link was sent to email',
        ];
    }

    public function postPasswordCodeAction(PasswordCode $passwordCodeForm)
    {
        /**
         * Receive email from sent data.
         */
        $data = $passwordCodeForm->getData();

        /**
         * Fetch user.
         */
        $user = (new Users())->where('email', $data['email'])->oneOrFail();

        /**
         * Set new password.
         */
        // $user->setAndSave(['password' => $this->auth('frontend')->hashPassword($data['password'])]);

        /**
         * Login user.
         */
        // $this->auth('frontend')->performLogin($user);

        return [
            'success' => true,
        ];
    }

    public function postResetPasswordAction(ResetPassword $resetPasswordForm)
    {
        return [
            'success' => true,
        ];
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
