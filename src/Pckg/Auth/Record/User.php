<?php

namespace Pckg\Auth\Record;

use Pckg\Auth\Entity\Users;
use Pckg\Database\Field\JsonObject;
use Pckg\Database\Record;

/**
 * Class User
 *
 * @package  Pckg\Auth\Record
 * @property JsonObject $oauth2
 * @property string $email
 * @property string $autologin
 * @property string $hash
 * @property string $password
 * @property int|null $user_group_id
 */
class User extends Record
{
    /**
     * @var string
     */
    protected $entity = Users::class;

    /**
     * @var string[]
     */
    protected $protect = [
        'password',
        'autologin',
        'oauth2',
        'deleted_at',
    ];

    /**
     * @var string[]
     */
    protected $encapsulate = [
        'oauth2' => JsonObject::class,
    ];

    /**
     * @return bool
     */
    public function isSuperadmin()
    {
        return in_array($this->user_group_id, [1]);
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return in_array($this->user_group_id, [1, 3]);
    }

    /**
     * @return bool
     */
    public function isCheckin()
    {
        return in_array($this->user_group_id, [5]);
    }

    /**
     * @return string
     */
    public function getAutologinUrlAttribute()
    {
        return config('url') . '/?' . $this->getAutologinParameterAttribute();
    }

    /**
     * @return string
     */
    public function getAutologinParameterAttribute()
    {
        if (!$this->autologin) {
            $this->setAndSave(['autologin' => sha1(microtime())]);
        }

        return config('pckg.auth.getParameter', 'autologin') . '=' . $this->autologin;
    }

    /**
     * @return string
     */
    public function getDashboardUrl()
    {
        return '/';
    }

    /**
     * @return $this
     */
    public function setDefaults()
    {
        $this->set(
            [
                'hash' => sha1(microtime()),
                'user_group_id' => 2,
                'language_id' => substr(localeManager()->getCurrent(), 0, 2),
            ]
        );

        if (!$this->autologin) {
            $this->set(
                [
                    'autologin' => sha1(microtime()),
                ]
            );
        }

        return $this;
    }

    /**
     * @param  false $dns
     * @return bool
     */
    public function getHasValidEmailAttribute($dns = false)
    {
        return isValidEmail($this->email);
    }

    /**
     * @return string|null
     */
    public function getOAuth2Token(string $provider)
    {
        return $this->oauth2->{$provider}->token ?? null;
    }
}
