<?php

namespace Pckg\Auth\Command;

use Pckg\Auth\Entity\Users;
use Pckg\Mail\Service\Mail\Adapter\User;
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

    protected $template = 'user-password-reset';

    public function __construct(\Pckg\Auth\Record\User $user)
    {
        $this->user = $user;
    }

    /**
     * @param $template
     *
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
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
         * Send email.
         */
        email($this->template, new User($this->user), [
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

        /**
         * We have generated code, we need to save code and timestamp to database and link it to user.
         * This code will expire after 24h.
         */
        UserPasswordReset::create([
            'user_id'    => $this->user->id,
            'created_at' => date('Y-m-d H:i:s'),
            'code'       => $code,
        ]);

        return $thi;
    }

}