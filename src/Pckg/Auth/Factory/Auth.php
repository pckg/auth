<?php namespace Pckg\Auth\Factory;

use Facebook\Facebook as FacebookApi;
use Facebook\InstantArticles\Client\Client;
use Facebook\InstantArticles\Client\Helper;
use Pckg\Auth\Service\Provider\Facebook as FacebookProvider;

/**
 * Class Auth
 *
 * @package Pckg\Auth\Factory
 */
class Auth
{

    /**
     * @return FacebookProvider
     */
    public static function getFacebookProvider()
    {
        return new FacebookProvider(static::getFacebookApi());
    }

    /**
     * @return FacebookApi
     */
    public static function getFacebookApi()
    {
        return new FacebookApi(config('pckg.auth.provider.facebook.config'));
    }

    /**
     * @return Client
     */
    public static function getFacebookInstantArticlesClient()
    {
        $userId = static::getFacebookApi()
            ->get('me', $_SESSION['Auth']['Facebook']['fb_access_token'])
            ->getDecodedBody()['id'];

        $pages = static::getFacebookApi()
            ->get($userId . '/accounts', $_SESSION['Auth']['Facebook']['fb_access_token'])
            ->getDecodedBody()['data'];
        $pageAccessToken = $pages[2]['access_token'];

        return Client::create(
            config('pckg.auth.provider.facebook.config.app_id'),
            config('pckg.auth.provider.facebook.config.app_secret'),
            $pageAccessToken,
            $pages[2]['id']
        );
    }

    /**
     * @return Helper
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public static function getFacebookInstantArticlesHelper()
    {
        return Helper::create(
            config('pckg.auth.provider.facebook.config.app_id'),
            config('pckg.auth.provider.facebook.config.app_secret')
        );
    }

}