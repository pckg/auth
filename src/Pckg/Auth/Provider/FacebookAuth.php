<?php namespace Pckg\Auth\Provider;

use Pckg\Auth\Controller\Facebook;
use Pckg\Framework\Provider;

class FacebookAuth extends Provider
{
    public function routes()
    {
        return ['url' => array_merge_array([
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
        ])];
    }

}