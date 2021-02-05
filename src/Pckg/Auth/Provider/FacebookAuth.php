<?php namespace Pckg\Auth\Provider;

use Pckg\Auth\Controller\Facebook;
use Pckg\Framework\Provider;

/**
 * Class FacebookAuth
 *
 * @package Pckg\Auth\Provider
 */
class FacebookAuth extends Provider
{
    /**
     * @return array
     */
    public function routes()
    {
        return ['url' => array_merge_array(
            [
                'controller' => Facebook::class,
            ], [
                '/login/facebook' => [
                    'view' => 'login',
                    'name' => 'login_facebook',
                ],
                '/takelogin/facebook' => [
                    'view' => 'takelogin',
                    'name' => 'takelogin_facebook',
                ],
            ]
        )];
    }

}