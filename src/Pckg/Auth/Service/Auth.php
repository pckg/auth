<?php

namespace Pckg\Auth\Service;

use Pckg\Concept\Reflect;

class Auth
{

    public $users;

    public $statuses;

    protected $provider;

    protected $providers = [];

    /**
     * @param ProviderInterface|mixed $provider
     *
     * @return $this
     */
    public function useProvider($provider, $providerKey = 'default')
    {
        if (is_string($provider)) {
            if (!array_key_exists($provider, $this->providers)) {
                $config = config('pckg.auth.providers.' . $provider);
                $this->providers[$provider] = Reflect::create($config['type'], [$this]);
            }

            $provider = $this->providers[$provider];
        } else {
            $this->providers[$providerKey] = $provider;
        }

        $this->provider = $provider;

        return $this;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function getProviderKey()
    {
        foreach ($this->providers as $key => $provider) {
            if ($provider == $this->provider) {
                return $key;
            }
        }

        return null;
    }

    // user object
    public function getUser()
    {
        return $this->getProvider()->getUserById($this->user('id'));
    }

    public function is()
    {
        return !!$this->getUser();
    }

    public function user($key = null)
    {
        $sessionUser = $this->getSessionProvider()['user'] ?? [];

        if (!$sessionUser) {
            return null;

        } else if (!$key) {
            return $sessionUser;
            
        }

        return array_key_exists($key, $sessionUser)
            ? $sessionUser[$key]
            : null;
    }

    public function makePassword($password, $hash = null)
    {
        $hash = is_null($hash) ? config("hash") : $hash;

        return sha1($password . $hash);
    }

    public function createPassword($length = 10)
    {
        $characters = str_split('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 1);
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[array_rand($characters)];
        }

        return $password;
    }

    public function login($email, $password, $hash = null)
    {
        $hash = is_null($hash)
            ? config('hash')
            : $hash;

        $rUser = $this->getProvider()
                      ->getUserByEmailAndPassword($email, $this->makePassword($password, $hash));

        if ($rUser) {
            return $this->performLogin($rUser);
        }

        return false;
    }

    public function autologin($autologin)
    {
        $rUser = $this->getProvider()
                      ->getUserByAutologin($autologin);

        if ($rUser) {
            return $this->performLogin($rUser);
        }

        return false;
    }

    public function setAutologin()
    {
        if (!$this->isLoggedIn()) {
            return;
        }

        setcookie(
            "pckg.auth.autologin",
            serialize(
                [
                    $this->getProviderKey() => [
                        'user_id' => sha1($this->user('id')),
                    ],
                ]
            ),
            time() + (24 * 60 * 60 * 365.25),
            "/"
        );
    }

    public function performLogin($user)
    {
        $providerKey = $this->getProviderKey();

        $sessionHash = sha1(microtime() . sha1($user->id));

        $_SESSION['Pckg']['Auth']['Provider'][$providerKey] = [
            "user"  => $user->toArray(),
            "hash"  => $sessionHash,
            "flags" => [],
        ];

        setcookie(
            "pckg_auth_provider_" . $providerKey,
            serialize(
                [
                    "user" => $user->id,
                    "hash" => $sessionHash,
                ]
            ),
            time() + (24 * 60 * 60 * 365.25),
            "/"
        );

        return true;
    }

    public function logout()
    {
        $providerKeys = array_keys($_SESSION['Pckg']['Auth']['Provider'] ?? []);
        unset($_SESSION['Pckg']['Auth']['Provider']);

        foreach ($providerKeys as $providerKey) {
            setcookie('pckg_auth_provider_' . $providerKey, null, time() - (24 * 60 * 60 * 365.25), '/');
        }

        setcookie('pckg_auth_autologin', null, time() - (24 * 60 * 60 * 365.25), '/');
    }

    public function getSessionProvider()
    {
        return $_SESSION['Pckg']['Auth']['Provider'][$this->getProviderKey()] ?? [];
    }

    public function isLoggedIn()
    {
        $providerKey = $this->getProviderKey();
        $sessionProvider = $this->getSessionProvider();

        /**
         * Session for provider does not exist.
         */
        if (!$sessionProvider) {
            return false;
        }

        /**
         * Session exists, but user doesn't.
         */
        if (!isset($sessionProvider['user']['id'])) {
            return false;
        }

        /**
         * Cookie for provider does not exist.
         */
        if (!isset($_COOKIE['pckg_auth_provider_' . $providerKey])) {
            return false;
        }

        $cookie = unserialize($_COOKIE['pckg_auth_provider_' . $providerKey]);

        /**
         * Cookie exists, but hash isn't set.
         */
        if (!isset($cookie['hash'])) {
            return false;
        }

        /**
         * Hash and user matches.
         */
        if ($cookie['hash'] === $sessionProvider['hash'] && $cookie['user'] === $sessionProvider['user']['id']) {
            return true;
        }

        return false;
    }

    public function isGuest()
    {
        return !$this->isLoggedIn();
    }

    public function getGroupId()
    {
        return $this->getSessionProvider()['user']['user_group_id'] ?? null;
    }
}
