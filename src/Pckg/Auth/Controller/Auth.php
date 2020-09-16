<?php

namespace Pckg\Auth\Controller;

use Derive\Orders\Record\Order;
use Pckg\Auth\Command\LoginUserViaForm;
use Pckg\Auth\Command\LogoutUser;
use Pckg\Auth\Command\RegisterUser;
use Pckg\Auth\Command\SendPasswordCode;
use Pckg\Auth\Entity\UserPasswordResets;
use Pckg\Auth\Entity\Users;
use Pckg\Auth\Factory\User;
use Pckg\Auth\Form\ForgotPassword;
use Pckg\Auth\Form\Login;
use Pckg\Auth\Form\PasswordCode;
use Pckg\Auth\Form\Register;
use Pckg\Auth\Form\ResetPassword;
use Pckg\Auth\Form\SignupUser;
use Pckg\Auth\Record\UserPasswordReset;
use Pckg\Auth\Service\Auth as AuthService;
use Pckg\Concept\Event\Dispatcher;
use Pckg\Framework\Controller;
use Pckg\Framework\Exception\NotFound;
use Pckg\Framework\Response;
use Pckg\Framework\Router;
use Pckg\Manager\Meta;

/**
 * Class Auth
 *
 * @package Pckg\Auth\Controller
 */
class Auth extends Controller
{

    public function getUserAction(Meta $meta)
    {
        $user = $this->auth()->getUser();

        return [
            'loggedIn' => $this->auth()->isLoggedIn(),
            'user'     => $user ? only($user->data(), ['id', 'email', 'name', 'surname', 'user_group_id']) : [],
            'csrf'     => $meta->getCsrfValue(),
        ];
    }

    public function getUserAddressesAction()
    {
        $user = $this->auth()->getUser();

        $addresses = $user ? $user->addresses : [];

        return [
            'addresses' => $addresses,
        ];
    }

    public function getLoginAction()
    {
        if (auth()->isLoggedIn()) {
            return $this->response()->redirect($this->auth()->getUser()->getDashboardUrl());
        } else if (config('pckg.auth.providers.frontend.inactive')) {
            return $this->response()->redirect('/');
        }

        return view('Pckg/Auth:login');
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
        })->onError(function($data) {
            if ($this->request()->isAjax()) {
                $this->response()->respondWithError(array_merge(['text' => __('pckg.auth.error')], $data ?? []));

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

    /**
     * @param SignupUser $signupUser
     *
     * @return array
     */
    function postSignupAction(SignupUser $signupUser)
    {
        $data = $signupUser->getData();

        $user = User::create([
                                 'email'    => $data['email'],
                                 'password' => auth()->hashPassword($data['password']),
                             ]);

        /**
         * We would probably like to notify user about account creation and let him confirm it?
         */
        email('user.registered', new \Pckg\Mail\Service\Mail\Adapter\User($user), [
            'data' => [
                'confirmAccountUrl' => url('pckg.auth.activate', ['activation' => sha1($user->hash . $user->autologin)],
                                           true),
            ],
        ]);

        return [
            'success' => true,
        ];
    }

    function getActivateAction($activation)
    {
        $user = (new Users())->where('password', null)
                             ->whereRaw('SHA1(CONCAT(users.hash, users.autologin)) = ?', [$activation])
                             ->one();
    }

    function getForgotPasswordAction(ForgotPassword $forgotPasswordForm)
    {
        return view("vendor/lfw/auth/src/Pckg/Auth/View/forgotPassword", [
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
         * Fetch last request.
         */
        $userPasswordReset = (new UserPasswordResets())->where('user_id', $user->id)->orderBy('id DESC')->one();
        if ($userPasswordReset && $userPasswordReset->hasRequestedTooSoon()) {
            return [
                'success' => false,
                'Please wait 5 minutes before making a new request.',
            ];
        }

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
        return [
            'success' => true,
        ];
    }

    public function postResetPasswordAction(ResetPassword $resetPasswordForm)
    {
        /**
         * Receive email from sent data.
         */
        $data = $resetPasswordForm->getData();

        /**
         * Fetch user.
         */
        $user = (new Users())->where('email', $data['email'])->oneOrFail();
        $code = (new UserPasswordResets())->joinUser()
                                          ->where('email', $data['email'])
                                          ->where('created_at', date('Y-m-d H:i:s', strtotime('-1day')), '>=')
                                          ->where('used_at', null)
                                          ->where('code', str_replace(' ', '', $data['code']))
                                          ->oneOrFail();

        /**
         * Set new password.
         */
        $user->setAndSave(['password' => $this->auth('frontend')->hashPassword($data['password'])]);
        $code->setAndSave(['used_at' => date('Y-m-d H:i:s')]);

        /**
         * Login user.
         */
        $this->auth('frontend')->performLogin($user);

        return [
            'success' => true,
        ];
    }

}
