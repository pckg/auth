<?php

namespace Pckg\Auth\Service\Provider;

use Exception;
use GuzzleHttp\Client;
use League\OAuth2\Client\Provider\GenericProvider;
use Pckg\Auth\Factory\User;
use Pckg\Auth\Service\Auth;

/**
 * Class OAuth2
 *
 * @package Pckg\Auth\Service\Provider
 */
class OAuth2 extends AbstractProvider
{
    /**
     * @var Auth
     */
    protected $auth;

    /**
     * @var ?GenericProvider
     */
    protected $oauth2;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Fetch the authorization URL from the provider; this returns the
     * urlAuthorize option and generates and applies any necessary parameters
     * (e.g. state).
     * Get the state generated for you and store it to the session.
     * Redirect the user to the authorization URL.
     */
    public function redirectToLogin($options = [])
    {
        /**
         * Can we check here if user has already authorized scopes?
         * Do we know which user was logged in?
         * We could add a cookie and display "not you" screen?
         */
        $provider = $this->getOAuth2Provider();

        if (!isset($options['scope'])) {
            $options['scope'] = $this->config['oauth2']['scopes'] ?? ['basic'];
        }

        $authorizationUrl = $provider->getAuthorizationUrl($options);
        session()->set('oauth2state', $provider->getState());

        response()->redirect($authorizationUrl);
    }

    public function logout()
    {
    }

    /**
     * @return GenericProvider
     */
    protected function getOAuth2Provider()
    {
        if (!$this->oauth2) {
            $config = $this->config['oauth2'];
            if (!($config['redirectUri'] ?? null)) {
                $config['redirectUri'] = url('oauth.provider', ['provider' => $this->identifier], true);
            }
            $this->oauth2 = new GenericProvider($config);
        }

        return $this->oauth2;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function refreshToken()
    {
        $existingAccessToken = session('oauth2refreshtoken');
        ddd('refreshing');

        if (!$existingAccessToken->hasExpired()) {
            return;
        }

        $newAccessToken = $this->getOAuth2Provider()->getAccessToken(
            'refresh_token',
            [
                'refresh_token' => $existingAccessToken->getRefreshToken()
            ]
        );

        // Purge old access token and store new access token to your data store.
        session()->set('oauth2accesstoken', $newAccessToken);
    }

    /**
     * Try to get an access token using the authorization code grant.
     * Save token to the session.
     *
     * @param  $code
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function fetchTokenFromCode($code)
    {
        $accessToken = $this->getOAuth2Provider()->getAccessToken(
            'authorization_code',
            [
                'code' => $code,
            ]
        );

        $token = $accessToken->getToken();
        session()->set('oauth2accesstoken', $token)
            ->set('oauth2refreshtoken', $accessToken->getRefreshToken())
            // @phpstan-ignore-next-line
            ->set('oauth2expires', strtotime($accessToken->getExpires()));

        return $token;
    }

    /**
     * @param  $oauth2token
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserFromRemote($oauth2token)
    {
        if (!$oauth2token) {
            throw new \Exception('No token');
        }

        $response = (new Client())->get(
            $this->config['oauth2']['urlResourceOwnerDetails'],
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $oauth2token,
                ],
                'timeout' => $this->config['httpOptions']['timeout'] ?? 10,
                'verify' => $this->config['httpOptions']['verify'] ?? true,
            ]
        );
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        return $data;
    }

    /**
     * Handle generic OAuth2 login procedure.
     */
    public function process()
    {
        $code = get('code', null);
        $state = get('state', null);
        $oauth2state = session('oauth2state');

        /**
         * If we don't have an authorization code then get one
         */
        if (!$code) {
            /**
             * Use referer for better redirect.
             */
            $referer = server('HTTP_REFERER');
            if ($referer) {
                session()->set('authReferer', $referer);
            } else {
                session()->delete('authReferer');
            }

            /**
             * What if we are already authenticated and have valid token?
             */
            $this->redirectToLogin();
        }

        /**
         * Check given state against previously stored one to mitigate CSRF attack
         */
        if (!$state || ($oauth2state && $state !== $oauth2state)) {
            session()->delete('oauth2state');

            exit('Invalid state');
        }

        /**
         * We want to save the token?
         */
        try {
            $token = $this->fetchTokenFromCode($code);
        } catch (\Throwable $e) {
            error_log(exception($e));
            throw new \Exception('Error fetching token from code');
        }

        /**
         * Fetch user and mark it as authenticated.
         */
        try {
            $user = $this->getUserFromRemote($token);
        } catch (\Throwable $e) {
            error_log(exception($e));
            throw new \Exception('Error fetching remote identity');
        }

        /**
         * We have to create user in our database.
         * And add a OAuth2 provider user ID.
         */
        $email = $user['user']['email'] ?? ($user['email'] ?? null);
        $remoteUserId = $user['user']['id'] ?? ($user['id'] ?? str_replace('/users/', '', $user['uri'] ?? null));

        if (!$email) {
            error_log(json_encode($user));
            throw new \Exception('Email not present in auth identity!');
        }

        /**
         * Trigger pre-validation event.
         */
        trigger(OAuth2::class . '.loggingIn', ['email' => $email]);

        $userRecord = $this->getUserByEmail($email);

        /**
         * Check if we're adding another connection.
         */
        if (auth()->isLoggedIn()) {
            if ($userRecord) {
                // different oauth and current users?
                // redirect to merge screen?
                if ($userRecord->id !== auth()->user('id')) {
                    throw new Exception('Logout and login with different account');
                } else {
                    // yeah, this is the correct user
                }
            } else {
                throw new Exception('Logout and create a separate account');
            }
        } else if (!$userRecord) {
            /**
             * Auto register user.
             */
            $userData = [
                'email' => $email,
            ];
            $userRecord = User::create($userData, $this->config['entity']);
        } else {
            // preset user record?
        }


        /**
         * Connect it to the user.
         * @deprecated
         */
        if ($this->identifier && !($user->{$this->identifier . '_user_id'} ?? null)) {
            $userRecord->setAndSave(
                [
                    $this->identifier . '_user_id' => $remoteUserId,
                ]
            );
        }

        /**
         * Update OAuth2 info.
         */
        $userRecord->oauth2->{$this->identifier} = [
            'id' => $remoteUserId,
            'token' => $token,
            'me' => $user,
        ];
        $userRecord->save();

        /**
         * Now login user.
         * We want to keep user logged in for concurrent sessions, so also set a cookie.
         */
        $this->auth->performLogin($userRecord);
        $this->auth->setAutologin(); // mark as social login?

        /**
         * Redirect user to the refering URL.
         */
        $referer = session()->get('authReferer', '/');
        if ($referer !== '/') {
            session()->delete('authReferer');
        }

        /**
         * When we open auth session in popup, we have to close it and refresh parent.
         */
        response()->respond('<script>try { if (window.opener.location.href !== window.location.href) { window.close(); } } catch (e) { console.log(e); } window.location.href = ' . json_encode($referer) . ';</script>');
        response()->redirect($referer);
    }
}
