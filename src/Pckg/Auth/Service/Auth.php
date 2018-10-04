<?php

namespace Pckg\Auth\Service;

use Pckg\Auth\Entity\Users;
use Pckg\Auth\Record\User;
use Pckg\Concept\Reflect;

class Auth
{

    public $users;

    public $statuses;

    protected $provider;

    protected $providers = [];

    protected $loggedIn = false;

    /**
     * @var User
     */
    protected $user;

    /**
     * @param ProviderInterface|mixed $provider
     *
     * @return $this
     */
    public function useProvider($provider, $providerKey = 'frontend')
    {
        if (is_string($provider)) {
            if (!array_key_exists($provider, $this->providers)) {
                $config = config('pckg.auth.providers.' . $provider);
                $this->providers[$provider] = Reflect::create($config['type'], [$this]);
                $this->providers[$provider]->setEntity($config['entity']);
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
        if (!$this->provider) {
            $this->useProvider('frontend');
        }

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
    /**
     * @return mixed|User
     */
    public function getUser()
    {
        if ($this->user) {
            return $this->user;
        }

        return $this->user = $this->getProvider()->getUserById($this->user('id'));
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

    public function hashedPasswordMatches($hashedPassword, $password)
    {
        $hash = config('pckg.auth.providers.' . $this->getProviderKey() . '.hash');

        return password_verify($password, $hashedPassword) || sha1($password . $hash) === $hashedPassword;
    }

    public function hashPassword($password)
    {
        $version = config('pckg.auth.providers.' . $this->getProviderKey() . '.version');
        $hash = config('pckg.auth.providers.' . $this->getProviderKey() . '.hash');

        /**
         * @T00D00 - at some point remove unsecure (sha1) version and add salt to password
         */
        if ($version != 'secure') {
            return sha1($password . $hash);
        }

        return password_hash($password, PASSWORD_DEFAULT);
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

    public function login($email, $password)
    {
        $rUser = $this->getProvider()
                      ->getUserByEmail($email);

        if (!$rUser || !$this->hashedPasswordMatches($rUser->password, $password)) {
            return false;
        }

        if ($rUser) {
            return $this->performLogin($rUser);
        }

        return false;
    }

    public function autologin($id)
    {
        $rUser = $this->getProvider()
                      ->getUserById($id);

        if ($rUser) {
            return $this->performLogin($rUser);
        }

        return false;
    }

    public function setParentLogin()
    {
        setcookie(
            "pckg_auth_parentlogin",
            json_encode(
                [
                    $this->getProviderKey() => [
                        'hash'    => sha1(config('security.hash') . $this->user('id')),
                        'user_id' => $this->user('id'),
                    ],
                ]
            ),
            time() + (24 * 60 * 60 * 365.25),
            "/"
        );
    }

    public function performAutologin()
    {
        if (!isset($_COOKIE['pckg_auth_autologin'])) {
            return;
        }

        $cookie = json_decode($_COOKIE['pckg_auth_autologin'], true);
        foreach ($cookie as $provider => $data) {
            if (isset($data['user_id']) && isset($data['hash'])
                && sha1(config('security.hash') . $data['user_id']) == $data['hash']
            ) {
                auth()->useProvider($provider);
                auth()->autologin($data['user_id']);
            }
        }
    }

    public function performParentLogin()
    {
        if (!isset($_COOKIE['pckg_auth_autologin'])) {
            return;
        }

        $cookie = json_decode($_COOKIE['pckg_auth_autologin'], true);
        foreach ($cookie as $provider => $data) {
            if (isset($data['user_id']) && isset($data['hash'])
                && sha1(config('security.hash') . $data['user_id']) == $data['hash']
            ) {
                auth()->useProvider($provider);
                auth()->autologin($data['user_id']);
            }
        }
    }

    /**
     * @T00D00
     */
    public function setAutologin()
    {
        setcookie(
            "pckg_auth_autologin",
            json_encode(
                [
                    $this->getProviderKey() => [
                        'hash'    => sha1(config('security.hash') . $this->user('id')),
                        'user_id' => $this->user('id'),
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
            json_encode(
                [
                    "user" => $user->id,
                    "hash" => $sessionHash,
                ]
            ),
            time() + (24 * 60 * 60 * 365.25),
            "/"
        );

        $this->loggedIn = true;

        $this->user = $user;

        trigger(Auth::class . '.userLoggedIn', [$user]);

        return true;
    }

    public function logout()
    {
        $providerKeys = array_keys($_SESSION['Pckg']['Auth']['Provider'] ?? []);
        unset($_SESSION['Pckg']['Auth']['Provider']);

        foreach ($providerKeys as $providerKey) {
            setcookie('pckg_auth_provider_' . $providerKey, null, time() - (24 * 60 * 60 * 365.25), '/');
        }

        if (isset($_COOKIE['pckg_auth_parentlogin'])) {
            $this->performParentLogin();
            setcookie('pckg_auth_parentlogin', null, time() - (24 * 60 * 60 * 365.25), '/');
        } else {
            setcookie('pckg_auth_autologin', null, time() - (24 * 60 * 60 * 365.25), '/');
        }

        $this->loggedIn = false;
        $this->user = null;
    }

    public function getSessionProvider()
    {
        return $_SESSION['Pckg']['Auth']['Provider'][$this->getProviderKey()] ?? [];
    }

    public function requestProvider()
    {
        if (!$this->provider) {
            $this->useProvider('frontend');
        }
    }

    public function isLoggedIn()
    {
        $this->requestProvider();
        $providerKey = $this->getProviderKey();
        $sessionProvider = $this->getSessionProvider();

        if ($this->loggedIn) {
            return true;
        }

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

        $cookie = json_decode($_COOKIE['pckg_auth_provider_' . $providerKey], true);

        /**
         * Cookie exists, but hash isn't set.
         */
        if (!isset($cookie['hash'])) {
            return false;
        }

        /**
         * Hash and user matches.
         */
        if ($cookie['hash'] != $sessionProvider['hash'] || $cookie['user'] != $sessionProvider['user']['id']) {
            return false;
        }

        /**
         * User exists in database.
         */
        if (!$this->user) {
            $this->user = (new Users())->nonDeleted()->where('id', $sessionProvider['user']['id'])->one();
        }

        if (!$this->user) {
            return false;
        }

        return true;
    }

    public function isGuest()
    {
        return !$this->isLoggedIn();
    }

    public function isAdmin()
    {
        return $this->isLoggedIn() && method_exists($this->getUser(), 'isAdmin') && $this->getUser()->isAdmin();
    }

    public function isCheckin()
    {
        return $this->isLoggedIn() && method_exists($this->getUser(), 'isCheckin') && $this->getUser()->isCheckin();
    }

    public function getGroupId()
    {
        $group = $this->getSessionProvider()['user'][config(
                'pckg.auth.providers.' . $this->getProviderKey() . '.userGroup'
            )] ?? null;

        return $group;
    }

    public function isEmail($email)
    {
        if (!is_array($email)) {
            $email = [$email];
        }

        return in_array($this->user('email'), $email);
    }

}
