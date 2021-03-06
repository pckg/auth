<?php

namespace Pckg\Auth\Service\Provider;

use Pckg\Auth\Entity\Users;
use Pckg\Auth\Record\User;

/**
 * Class Facebook
 *
 * @package Pckg\Auth\Service\Provider
 */
class Facebook extends AbstractProvider
{

    /**
     * @var \Facebook\Facebook
     */
    protected $facebook;

    /**
     * @var array
     */
    protected $permissions = [];

    /**
     * @var \Facebook\Helpers\FacebookRedirectLoginHelper
     */
    protected $redirectLoginHelper;

    /**
     * @var string
     */
    protected $entity = Users::class;

    public function __construct(\Facebook\Facebook $facebook)
    {
        $this->facebook = $facebook;

        $this->redirectLoginHelper = $this->facebook->getRedirectLoginHelper();

        $this->initPermissions();
    }

    /**
     * @return \Facebook\Facebook
     */
    public function getApi()
    {
        return $this->facebook;
    }

    public function initPermissions()
    {
        $this->permissions = [
            'email', /*'manage_pages', 'pages_manage_instant_articles',*/
            'pages_show_list',
        ];
    }

    public function redirectToLogin($options = [])
    {
        $loginUrl = $this->redirectLoginHelper->getLoginUrl(
            url('takelogin_facebook', [], true),
            $this->permissions
        );

        response()->redirect($loginUrl);
    }

    /**
     * @return \Facebook\Authentication\AccessToken|null
     * @throws \Facebook\Exceptions\FacebookResponseException
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    private function getAccessToken()
    {
        try {
            $accessToken = $this->redirectLoginHelper->getAccessToken();

            return $accessToken;
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            throw $e;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            throw $e;
        }
    }

    /**
     * @param  $accessToken
     * @throws \Exception
     */
    private function checkAccessToken($accessToken)
    {
        if (!$accessToken) {
            if ($this->redirectLoginHelper->getError()) {
                throw new \Exception(
                    $this->redirectLoginHelper->getError() . ' ' . $this->redirectLoginHelper->getErrorCode() . ' ' .
                    $this->redirectLoginHelper->getErrorReason() . ' ' .
                    $this->redirectLoginHelper->getErrorDescription()
                );
            } else {
                throw new \Exception('No access token');
            }
        }
    }

    /**
     * @param  $accessToken
     * @return \Facebook\Authentication\AccessToken
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    private function setLongLived($accessToken)
    {
        $oAuth2Client = $this->facebook->getOAuth2Client();

        if (!$accessToken->isLongLived()) {
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (\Facebook\Exceptions\FacebookSDKException $e) {
                throw $e;
            } finally {
                return $accessToken;
            }
        }

        return $accessToken;
    }

    /**
     * @return bool
     * @throws \Facebook\Exceptions\FacebookResponseException
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function handleTakelogin()
    {
        //$helper = $this->facebook->getRedirectLoginHelper();
        /**
         * Make access token actions.
         */
        $accessToken = $this->getAccessToken();
        $this->checkAccessToken($accessToken);
        $accessToken = $this->setLongLived($accessToken);

        /**
         * Get user data from facebook.
         */
        $body = $this->facebook->get('me?fields=name,email', $accessToken)->getDecodedBody();
        $email = $body['email'];
        $fbUserId = $body['id'];

        /**
         * Link user to facebook user and save long live token.
         */
        (new Users())->where('id', auth()->user('id'))->oneAndIf(
            function (User $user) use ($fbUserId, $accessToken) {
                return $user->setAndSave(['fb_user_id' => $fbUserId, 'fb_long_live_token' => $accessToken]);
            }
        );

        /**
         * Save to session for later use.
         */
        session()->auth->fb->fb_access_token = (string)$accessToken;
        $_SESSION['Auth']['Facebook']['fb_access_token'] = (string)$accessToken;

        return !!$accessToken;
    }

    public function getUser()
    {
    }

    public function logout()
    {
    }
}
