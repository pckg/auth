<?php

namespace Pckg\Auth\Service;

use Pckg\Auth\Service\Provider\Database;
use Pckg\Auth\Service\Provider\Facebook;
use Pckg\Concept\Reflect;
use Pckg\Framework\Config;

class Auth
{

    public $users;

    public $statuses;

    private $user;

    protected $provider;

    public function __construct(Config $config)
    {
        $this->config = $config;

        $this->useDatabaseProvider();
    }

    public function useProvider(ProviderInterface $provider)
    {
        $this->provider = $provider;

        return $this;
    }

    public function useDatabaseProvider()
    {
        $this->provider = Reflect::create(Database::class, [$this]);

        return $this;
    }

    public function useFacebookProvider($fbApi)
    {
        $this->provider = Reflect::create(Facebook::class, $fbApi);

        return $this;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function addFlag($flags = [])
    {
        if (!is_array($flags)) {
            $flags = (array)$flags;
        }

        foreach ($flags AS $flag) {
            if (!in_array($flag, $_SESSION['Auth']['flags'])) {
                $_SESSION['Auth']['flags'][] = $flag;
            }
        }
    }

    public function hasFlag($flags = [])
    {
        if (!isset($_SESSION['Auth']['flags'])) {
            return false;
        }

        if (!is_array($flags)) {
            $flags = (array)$flags;
        }

        foreach ($flags AS $flag) {
            if (!in_array($flag, $_SESSION['Auth']['flags'])) {
                return false;
            }
        }

        return true;
    }

    public function removeFlag($flags = [])
    {
        foreach ($flags AS $flag) {
            if (($key = array_search($flag, $_SESSION['Auth']['flags'])) !== false) {
                unset($_SESSION['Auth']['flags'][$key]);
            }
        }
    }

    private function setUsersEntity()
    {
        $config = $this->config->get();
        $authConfig = $config["defaults"]["auth"];

        return $this->users = new $authConfig["user"]["entity"];
    }

    private function setUserRecord()
    {
        $config = $this->config->get();
        $authConfig = $config["defaults"]["auth"];

        return $this->user = new $authConfig["user"]["record"];
    }

    // user object
    public function getUser()
    {
        return $this->getProvider()->getUser();
    }

    public function is()
    {
        return !!$this->getUser();
    }

    public function user($key = null)
    {
        return isset($_SESSION['User'])
            ? ($key
                ? (isset($_SESSION['User'][$key])
                    ? $_SESSION['User'][$key]
                    : null)
                : $_SESSION['User']
            )
            : null;
    }

    public function makePassword($password, $hash = null)
    {
        $hash = is_null($hash) ? $this->config->get("hash") : $hash;

        return sha1($password . $hash);
    }

    public function login($email, $password, $hash = null)
    {
        $hash = is_null($hash) ? $this->config->get("hash") : $hash;

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
        setcookie(
            "autologin",
            $_SESSION['Auth']['user_id'],
            time() + (24 * 60 * 60 * 365.25),
            "/"
        );
    }

    public function performLogin($rUser)
    {
        $sessionHash = sha1(microtime() . sha1($rUser->id));
        $dtIn = date("Y-m-d H:i:s");

        $_SESSION['User'] = $rUser->toArray();

        $_SESSION['Auth'] = [
            "user_id" => $rUser->id,
            "dt_in"   => $dtIn,
            "dt_out"  => '0000-00-00 00:00:00',
            "hash"    => $sessionHash,
            "ip"      => $_SERVER['REMOTE_ADDR'],
            "flags"   => [],
        ];

        setcookie(
            "LFW",
            serialize(["hash" => $sessionHash]),
            time() + (24 * 60 * 60 * 365.25),
            "/"
        );

        return true;
    }

    public function logout()
    {
        unset($_SESSION['User']);
        unset($_SESSION['Auth']);
        setcookie('LFW', null, time() - (24 * 60 * 60 * 365.25), '/');
        setcookie('autologin', null, time() - (24 * 60 * 60 * 365.25), '/');
    }

    public function isLoggedIn($try = false)
    {
        // valid session login
        $sessionLogin = !empty($_SESSION['Auth']['user_id'])
                        && !empty($_SESSION['User']['id'])
                        && !empty($_COOKIE['LFW'])
                        && ($cookie = unserialize($_COOKIE['LFW']))
                        && $cookie['hash'] == $_SESSION['Auth']['hash'];

        if ($sessionLogin) {
            return true;
        }

        $this->logout();

        return false;
    }

    public function isGuest()
    {
        return !$this->isLoggedIn();
    }

    public function getGroupId()
    {
        return $_SESSION['User']['user_group_id'] ?? null;
    }
}
