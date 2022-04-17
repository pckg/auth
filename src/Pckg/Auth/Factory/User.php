<?php

namespace Pckg\Auth\Factory;

/**
 * Class User
 *
 * @package Pckg\Auth\Factory
 */
class User
{
    /**
     * @param  array $data
     * @return \Pckg\Auth\Record\User|\Pckg\Database\Record
     */
    public static function create(array $data = [], ?string $entity = null)
    {
        $data = array_merge(
            $data,
            [
                'enabled' => 1,
                'user_group_id' => 2,
                'language_id' => localeManager()->getCurrent()->slug ?? null,
                'hash' => sha1(microtime()),
                'autologin' => sha1(microtime()),
            ]
        );

        $user = \Pckg\Auth\Record\User::createNew($data, $entity ? resolve($entity) : null)->save();

        trigger(User::class . '.created', [$user]);

        return $user;
    }
}
