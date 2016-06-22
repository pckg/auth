<?php

namespace Pckg\Auth\Service;

use Pckg\Concept\Reflect;
use Pckg\Framework\Config;
use Pckg\Auth\Service\Provider\Facebook;

class Auth
{

    public $users;

    public $statuses;

    private $user;

    protected $provider;

    public function __construct(Config $config) {
        $this->config = $config;
    }

    public function useProvider(ProviderInterface $provider) {
        $this->provider = $provider;

        return $this;
    }

    public function useFacebookProvider($fbApi) {
        $this->provider = Reflect::create(Facebook::class, $fbApi);

        return $this;
    }

    public function getProvider() {
        return $this->provider;
    }

    public function addFlag($flags = []) {
        if (!is_array($flags)) {
            $flags = (array)$flags;
        }

        foreach ($flags AS $flag) {
            if (!in_array($flag, $_SESSION['Auth']['flags'])) {
                $_SESSION['Auth']['flags'][] = $flag;
            }
        }
    }

    public function hasFlag($flags = []) {
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

    public function removeFlag($flags = []) {
        foreach ($flags AS $flag) {
            if (($key = array_search($flag, $_SESSION['Auth']['flags'])) !== false) {
                unset($_SESSION['Auth']['flags'][$key]);
            }
        }
    }

    private function setUsersEntity() {
        $config = $this->config->get();
        $authConfig = $config["defaults"]["auth"];

        return $this->users = new $authConfig["user"]["entity"];
    }

    private function setUserRecord() {
        $config = $this->config->get();
        $authConfig = $config["defaults"]["auth"];

        return $this->user = new $authConfig["user"]["record"];
    }

    // user object
    public function getUser() {
        if ($this->user) {
            return $this->user;
        }

        if (!$this->users) {
            self::setUsersEntity();
        }

        return $this->user = $this->users->where(
            "id",
            isset($_SESSION['Auth']['user_id']) ? $_SESSION['Auth']['user_id'] : -1
        )->findOne() ?: self::setUserRecord();
    }

    public function is() {
        return !!$this->getUser();
    }

    public function user($key = null) {
        return isset($_SESSION['User'])
            ? ($key
                ? (isset($_SESSION['User'][$key])
                    ? $_SESSION['User'][$key]
                    : null)
                : $_SESSION['User']
            )
            : null;
    }

    public function makePassword($password, $hash = null) {
        $hash = is_null($hash) ? $this->config->get("hash") : $hash;

        return sha1($password . $hash);
    }

    public function login($email, $password, $hash = null) {
        $hash = is_null($hash) ? $this->config->get("hash") : $hash;

        $config = $this->config->get();

        if (!isset($config['defaults']['auth']['user']['entity'])) {
            throw new \Exception("Auth user entity not set (config).");
        }

        $this->users = new $config['defaults']['auth']['user']['entity'];

        $rUser = $this->users->where("email", $email)->where(
            "password",
            self::makePassword($password, $hash)
        )->findOne();

        if ($rUser) {
            return self::performLogin($rUser);
        }

        return false;
    }

    public function loginByUserID($id) {
        $sql = "SELECT u.*
			FROM users u
			WHERE u.id = '" . $id . "'";
        $q = context()->getDB()->query($sql);
        $r = context()->getDB()->fetch($q);

        if ($r) {
            return self::performLogin($r);
        }

        return false;
    }

    public function loginByAutologin($autologin) {
        $sql = "SELECT u.*
			FROM users u
			INNER JOIN lfw_users_autologin ua ON (ua.user_id = u.id AND ua.active = 1)
			WHERE ua.autologin = '" . context()->getDB()->escape($autologin) . "'";
        $q = context()->getDB()->query($sql);
        $r = context()->getDB()->fetch($q);

        if ($r) {
            return self::performLogin($r);
        }

        return false;
    }

    public function hashLogin($hash) {
        $sql = "SELECT u.*
			FROM lfw_users u
			INNER JOIN lfw_logins l ON (l.user_id = u.id)
			WHERE l.hash = '" . context()->getDB()->escape($hash) . "' 
			AND l.dt_out = '0000-00-00 00:00:00'
			AND l.ip = '" . $_SERVER['REMOTE_ADDR'] . "'";
        $q = context()->getDB()->query($sql);
        $r = context()->getDB()->fetch($q);

        if ($r) {
            return self::performLogin($r);
        }

        return false;
    }

    public function performLogin($rUser) {
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

        $config = $this->config->get();
        setcookie(
            "LFW",
            serialize(["hash" => $sessionHash]),
            time() + (24 * 60 * 60 * 365.25),
            "/"/*, $config['defaults']['domain']*/
        );

        return true;
    }

    public function logout() {
        unset($_SESSION['User']);
        unset($_SESSION['Auth']);
        unset($_COOKIE['LFW']);
    }

    public function isLoggedIn($try = false) {
        // valid session login
        $sessionLogin = !empty($_SESSION['Auth']['user_id'])
                        && !empty($_SESSION['User']['id'])
                        && !empty($_COOKIE['LFW'])
                        && ($cookie = unserialize($_COOKIE['LFW']))
                        && $cookie['hash'] == $_SESSION['Auth']['hash'];

        if ($sessionLogin) {
            return true;
        }

        if (!$try) {
            return false;
        }

        // cookie login
        $cookieLogin = (isset($_COOKIE['LFW']) && ($cookie = unserialize($_COOKIE['LFW'])) && isset($cookie['hash']))
            ? self::loginByAutologin($cookie['hash'])
            : false;

        if ($cookieLogin) {
            return true;
        }

        return false;
    }

    public function getGroupId() {
        return 1;
    }
}

?>