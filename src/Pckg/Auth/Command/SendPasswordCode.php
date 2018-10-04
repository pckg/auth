<?php

namespace Pckg\Auth\Command;

use Derive\Orders\Entity\Users;
use Derive\User\Service\Mail\User;
use Pckg\Auth\Record\UserPasswordReset;
use Pckg\Concept\CommandInterface;

/**
 * Class SendPasswordCode
 *
 * @package Pckg\Auth\Command
 */
class SendPasswordCode
{

    /**
     * @var \Pckg\Auth\Record\User
     */
    protected $user;

    public function __construct(\Pckg\Auth\Record\User $user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        /**
         * Generate random code XXX XXX
         * Make sure to use length of 6, 8, 10, otherwise this won't work.
         */
        $min = 0;
        $max = 999999;
        $num = rand($min, $max);
        $code = str_pad($num, strlen($max), '0', STR_PAD_LEFT);
        $niceCode = implode(' ', str_split($code, strlen($max) / 2));

        /**
         * We have generated code, we need to save code and timestamp to database and link it to user.
         * This code will expire after 24h.
         */
        UserPasswordReset::create([
                                      'user_id'    => $this->user->id,
                                      'created_at' => date('Y-m-d H:i:s'),
                                      'code'       => $code,
                                  ]);

        /**
         * Send email.
         */
        email('user-password-reset',
              new User($this->user),
              [
                  'data'  => [
                      'niceCode' => $niceCode,
                      'code'     => $code,
                      'link'     => config('url') . '#passwordSent-' . $this->user->email . '-' . $code,
                  ],
                  'fetch' => [
                      'user' => [
                          Users::class => $this->user->id,
                      ],
                  ],
              ]);

        return $thi;
    }

}