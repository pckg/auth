<?php namespace Pckg\Auth\Factory;

class User
{

    public static function create(array $data = [])
    {
        $data = array_merge($data, [
            'enabled'       => 1,
            'user_group_id' => 2,
            'language_id'   => localeManager()->getCurrent()->slug,
            'hash'          => sha1(microtime()),
            'autologin'     => sha1(microtime()),
        ]);

        return \Pckg\Auth\Record\User::create($data);
    }

}