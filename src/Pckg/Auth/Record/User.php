<?php namespace Pckg\Auth\Record;

use Pckg\Auth\Entity\Users;
use Pckg\Database\Record;

/**
 * Class User
 *
 * @package Pckg\Auth\Record
 */
class User extends Record
{

    protected $entity = Users::class;

    public function isAdmin()
    {
        return in_array($this->user_group_id, [1, 3]);
    }

    public function isCheckin()
    {
        return in_array($this->user_group_id, [5]);
    }

    public function getAutologinUrlAttribute()
    {
        return config('url') . '/?' . $this->getAutologinParameterAttribute();
    }

    public function getAutologinParameterAttribute()
    {
        if (!$this->autologin) {
            $this->setAndSave(['autologin' => sha1(microtime())]);
        }

        return config('pckg.auth.getParameter', 'autologin') . '=' . $this->autologin;
    }

    public function getDashboardUrl()
    {
        return '/';
    }

    public function setDefaults()
    {
        $this->set([
                       'hash'        => sha1(microtime()),
                       'user_group_id'   => 2,
                       'language_id' => substr(localeManager()->getCurrent(), 0, 2),
                       'autologin'   => sha1(microtime()),
                   ]);

        return $this;
    }

}