<?php

namespace Pckg\Auth\Service;

use http\Exception;
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
        if (is_object($provider)) {
            $this->provider = $provider;
            return $this;
        }

        if (array_key_exists($provider, $this->providers)) {
            $this->provider = $this->providers[$provider];
            return $this;
        }

        if (!is_string($provider)) {
            throw new \Exception('Invalid provider');
        }

        $config = config('pckg.auth.providers.' . $provider);
        if (!$config) {
            throw new \Exception('Empty provider config');
        }
        
        $this->providers[$provider] = Reflect::create($config['type'], [$this]);
        $this->providers[$provider]->setIdentifier($provider);
        $this->providers[$provider]->applyConfig($config);

        $this->provider = $this->providers[$provider];

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

    public function getUserDataArray()
    {
        $user = $this->getUser();
        $data = $user ? $user->jsonSerialize() : [];
        $data['tags'] = collect(config('pckg.auth.tags', []))->map(function($callable, $tag) {
            if (!Reflect::call($callable)) {
                return;
            }

            return $tag;
        })->removeEmpty()->values();

        /**
         * We want to enrich our user with some custom values.
         */
        trigger(Auth::class .'.getUserDataArray', ['user' => $user, 'data' => $data, 'setter' => function($newData) use (&$data){
            foreach ($newData as $key => $val) {
                $data[$key] = $val;
            }
        }]);

        return $data;
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
        } elseif (!$key) {
            return $sessionUser;
        }

        return array_key_exists($key, $sessionUser) ? $sessionUser[$key] : null;
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
         * @T00D00 ... add salt to password
         */
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
        $rUser = $this->getProvider()->getUserByEmail($email);

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
        $rUser = $this->getProvider()->getUserById($id);

        if ($rUser) {
            return $this->performLogin($rUser);
        }

        return false;
    }

    public function getSecurityHash()
    {
        $hash = config('security.hash', null);

        if (dev() && strlen($hash) < 64) {
            message('Make sure security hash is set (min length 64, current length ' . strlen($hash) . '!');
        }

        return $hash;
    }

    public function setParentLogin()
    {
        $this->setCookie("pckg_auth_parentlogin", base64_encode(json_encode([
                                                           $this->getProviderKey() => [
                                                               'hash'    => password_hash($this->getSecurityHash() .
                                                                                          $this->user('id') .
                                                                                          $this->user('autologin'),
                                                                                          PASSWORD_DEFAULT),
                                                               'user_id' => $this->user('id'),
                                                           ],
                                                       ])), time() + (60 * 60));
    }

    public function performAutologin()
    {
        if (!isset($_COOKIE['pckg_auth_autologin'])) {
            return;
        }

        $cookie = json_decode(base64_decode($_COOKIE['pckg_auth_autologin']), true);
        if (!$cookie) {
            return;
        }
        $this->performLoginFromStorage($cookie);
    }

    public function performParentLogin()
    {
        if (!isset($_COOKIE['pckg_auth_parentlogin'])) {
            return;
        }

        $cookie = json_decode(base64_decode($_COOKIE['pckg_auth_parentlogin']), true);
        if (!$cookie) {
            return;
        }
        $this->performLoginFromStorage($cookie);
    }

    protected function performLoginFromStorage(array $storage)
    {
        foreach ($storage as $provider => $data) {
            if (!is_array($data)) {
                continue;
            }

            $userId = $data['user_id'] ?? null;
            $hash = $data['hash'] ?? null;

            if (!$userId || !$hash) {
                continue;
            }

            $this->useProvider($provider);

            $user = $this->getProvider()->getUserById($userId);

            if (!$user) {
                continue;
            }

            $entry = $this->getSecurityHash() . $user->id . $user->autologin;
            if (!password_verify($entry, $hash)) {
                continue;
            }

            $this->performLogin($user);

            return true;
        }
    }

    /**
     * @T00D00
     */
    public function setAutologin()
    {
        $this->setCookie("pckg_auth_autologin", base64_encode(json_encode([
                                                         $this->getProviderKey() => [
                                                             'hash'    => password_hash($this->getSecurityHash() .
                                                                                        $this->user('id') .
                                                                                        $this->user('autologin'),
                                                                                        PASSWORD_DEFAULT),
                                                             'user_id' => $this->user('id'),
                                                         ],
                                                     ])), time() + (24 * 60 * 60 * 365.25));
    }

    public function authenticate($user)
    {
        $providerKey = $this->getProviderKey();
        $sessionHash = password_hash($this->getSecurityHash() . session_id(), PASSWORD_DEFAULT);

        $_SESSION['Pckg']['Auth']['Provider'][$providerKey] = [
            "user"  => $user->toArray(),
            "hash"  => $sessionHash,
            "flags" => [],
        ];

        $this->loggedIn = true;
        $this->user = $user;

        trigger(Auth::class . '.userLoggedIn', [$user]);
    }

    public function getUserSecuritySessionPass($user)
    {
        return $this->getSecurityHash() . '_' . $user->id . '_' . session_id();
    }

    public function regenerateSession()
    {
        $_SESSION['deactivated'] = time();
        session_regenerate_id();
        unset($_SESSION['deactivated']);
    }

    public function performLogin($user)
    {
        $providerKey = $this->getProviderKey();

        $this->loggedIn = true;

        $this->user = $user;

        /**
         * Get new session id specific to the $user.
         */
        $this->regenerateSession();

        /**
         * Generate session hash for user for current session.
         */
        $sessionHash = password_hash($this->getUserSecuritySessionPass($user), PASSWORD_DEFAULT);

        $_SESSION['Pckg']['Auth']['Provider'][$providerKey] = [
            "user"  => $user->toArray(),
            "hash"  => $sessionHash,
            "date" => date('Y-m-d H:i:s'),
            "flags" => [],
        ];

        $this->setCookie("pckg_auth_provider_" . $providerKey, base64_encode(json_encode([
                                                                        "user" => $user->id,
                                                                        "hash" => $sessionHash,
                                                                        "date" => date('Y-m-d H:i:s'),
                                                                    ])), time() + (24 * 60 * 60 * 365.25));

        trigger(Auth::class . '.userLoggedIn', [$user]);

        return true;
    }

    public function logout()
    {
        $providerKeys = array_keys($_SESSION['Pckg']['Auth']['Provider'] ?? []);
        unset($_SESSION['Pckg']['Auth']['Provider']);
        $this->regenerateSession();

        foreach ($providerKeys as $providerKey) {
            $this->setCookie('pckg_auth_provider_' . $providerKey, null, time() - (24 * 60 * 60 * 365.25));
        }

        if (isset($_COOKIE['pckg_auth_parentlogin'])) {
            $this->performParentLogin();
            $this->setCookie('pckg_auth_parentlogin', null, time() - (24 * 60 * 60 * 365.25));
        } else {
            $this->setCookie('pckg_auth_autologin', null, time() - (24 * 60 * 60 * 365.25));
        }

        $this->loggedIn = false;
        $this->user = null;
    }

    public function setCookie($name, $value, $time)
    {
        /**
         * There are issues with some version?
         */
        if (PHP_VERSION_ID >= 70300) {
            setcookie($name, $value, [
                'expires' => $time,
                'path' => '/',
                'secure' => true,
                'samesite' => 'strict',
            ]);
            return;
        }

        setcookie($name, $value, $time, '/; samesite=strict', '', true, true);
        //setcookie($name, $value, $time, '/; samesite=strict', server('HTTP_HOST'), true, true);
        //setcookie($name, $value, $time, '/', '', true, true);
    }

    public function getSessionProvider()
    {
        return $_SESSION['Pckg']['Auth']['Provider'][$this->getProviderKey()] ?? [];
    }

    public function requestProvider()
    {
        if ($this->provider) {
            return;
        }
        
        $this->useProvider(array_keys($_SESSION['Pckg']['Auth']['Provider'] ?? [])[0] ?? 'frontend');
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
        $cookieKey = 'pckg_auth_provider_' . $providerKey;
        if (!isset($_COOKIE[$cookieKey])) {
            return false;
        }

        $cookie = json_decode(base64_decode($_COOKIE[$cookieKey]), true);
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

        if (!password_verify($this->getUserSecuritySessionPass($this->user), $sessionProvider['hash'])) {
            return false;
        }

        return true;
    }

    public function isGuest()
    {
        return !$this->isLoggedIn();
    }

    public function isSuperadmin()
    {
        $is = $this->isLoggedIn() && method_exists($this->getUser(), 'isSuperadmin') && $this->getUser()->isSuperadmin();

        return $is;
    }

    public function isAdmin()
    {
        $is = $this->isLoggedIn() && method_exists($this->getUser(), 'isAdmin') && $this->getUser()->isAdmin();

        return $is;
    }

    public function isCheckin()
    {
        return $this->isLoggedIn() && method_exists($this->getUser(), 'isCheckin') && $this->getUser()->isCheckin();
    }

    public function getGroupId()
    {
        $group = $this->getSessionProvider()['user'][config('pckg.auth.providers.' . $this->getProviderKey() .
                                                            '.userGroup')] ?? null;

        return $group;
    }

    public function isEmail($email)
    {
        if (!is_array($email)) {
            $email = [$email];
        }

        return in_array($this->user('email'), $email);
    }

    public function getNewInternalGetParameter()
    {
        $data = [
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        $data['hash'] = auth()->hashPassword(config('identifier', null) . $data['timestamp'] . $this->getSecurityHash());
        $data['signature'] = sha1(json_encode($data));

        return base64_encode(json_encode($data));
    }

}
