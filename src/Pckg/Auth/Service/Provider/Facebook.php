<?php

namespace Pckg\Auth\Service\Provider;

use Pckg\Framework\Request\Data\Session;
use Pckg\Framework\Response;
use Pckg\Framework\Router;
use Pckg\Auth\Service\ProviderInterface;

class Facebook implements ProviderInterface
{

    protected $facebook;

    protected $permissions = [];

    public function __construct(\Facebook\Facebook $facebook, Response $response, Router $router, Session $session)
    {
        $this->facebook = $facebook;
        $this->response = $response;
        $this->router = $router;
        $this->session = $session;

        $this->redirectLoginHelper = $this->facebook->getRedirectLoginHelper();
    }

    public function initPermissions()
    {
        $this->permissions = ['email'];
    }

    public function redirectToLogin()
    {
        $loginUrl = $this->redirectLoginHelper->getLoginUrl($this->router->make('takelogin_facebook', [], true), $this->permissions);

        $this->response->redirect($loginUrl);
    }

    private function getAccessToken()
    {
        try {
            $accessToken = $this->redirectLoginHelper->getAccessToken();

        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            throw $e;

        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            throw $e;

        } finally {
            return $accessToken;

        }
    }

    private function checkAccessToken($accessToken)
    {
        if (!$accessToken) {
            if ($this->redirectLoginHelper->getError()) {
                throw new \Exception($this->redirectLoginHelper->getError() . ' ' . $this->redirectLoginHelper->getErrorCode() . ' ' . $this->redirectLoginHelper->getErrorReason() . ' ' . $this->redirectLoginHelper->getErrorDescription());

            } else {
                throw new \Exception('No access token');

            }
        }
    }

    private function setLongLived($accessToken)
    {
        $oAuth2Client = $this->facebook->getOAuth2Client();

        if (!$accessToken->isLongLived()) {
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                throw $e;

            } finally {
                return $accessToken;
            }
        }

        return $accessToken;
    }

    public function handleTakelogin()
    {
        $helper = $this->facebook->getRedirectLoginHelper();

        $accessToken = $this->getAccessToken();
        $this->checkAccessToken($helper, $accessToken);
        $accessToken = $this->setLongLived($accessToken);

        $this->session->auth->fb->fb_access_token = (string)$accessToken;

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