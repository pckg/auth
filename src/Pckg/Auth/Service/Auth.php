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

    const GEN_ALPHANUM = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

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
            throw new \Exception('Empty provider config (' . $provider . ')');
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
            $this->useProvider($this->getProviderKey() ?? 'frontend');
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

    public function createPassword($length = 10, $chars = self::GEN_ALPHANUM)
    {
        $characters = str_split($chars, 1);
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

    /**
     * Decode cookie with value, signature and host values.
     *
     * @param string $name
     * @return mixed|null
     */
    public function getSecureCookie(string $name)
    {
        $value = cookie()->get($name);
        if (!$value) {
            return null;
        }

        /**
         * Check that required keys exist.
         */
        $decoded = json_decode(base64_decode($value), true);
        if (!isset($decoded['value']) || !isset($decoded['signature']) || !isset($decoded['host'])) {
            // old cookie, delete?
            return null;
        }

        /**
         * Check that signature matches and that we are actually on the same host.
         */
        if (!$this->hashedPasswordMatches($decoded['signature'], $decoded['value'] . $decoded['host'] . $name)) {
            return null;
        }

        return json_decode(base64_decode($decoded['value']), true);
    }

    /**
     * Set cookie with value, signature and host to validate them later.
     *
     * @param string $name
     * @param $value
     */
    public function setSecureCookie(string $name, $value, $duration)
    {
        /**
         * Delete cookie when empty value or negative duration.
         */
        if (!$value || $duration < 0) {
            cookie()->set($name, null, (-24 * 60 * 60 * 365.25));
            return;
        }

        /**
         * Encode cookie elsewise.
         */
        $host = server('HTTP_HOST', null);
        $encoded = base64_encode(json_encode($value));
        $signature = $this->hashPassword($encoded . $host . $name);
        $value = base64_encode(json_encode([
            'value' => $encoded,
            'signature' => $signature,
            'host' => $host,
        ]));

        cookie()->set($name, $value, $duration);
    }

    public function setParentLogin()
    {
        $this->setSecureCookie('pckg_auth_parentlogin', [
            $this->getProviderKey() => [
                'hash'    => password_hash($this->getSecurityHash() .
                    $this->user('id') .
                    $this->user('autologin'),
                    PASSWORD_DEFAULT),
                'user_id' => $this->user('id'),
            ],
        ], (60 * 60));
    }

    public function performAutologin()
    {
        $cookie = $this->getSecureCookie('pckg_auth_autologin');
        if (!$cookie) {
            return;
        }
        $this->performLoginFromStorage($cookie);
    }

    public function performParentLogin()
    {
        $cookie = $this->getSecureCookie('pckg_auth_parentlogin');
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
        $this->setSecureCookie('pckg_auth_autologin', [
            $this->getProviderKey() => [
                'hash' => password_hash($this->getSecurityHash() .
                    $this->user('id') .
                    $this->user('autologin'),
                    PASSWORD_DEFAULT),
                'user_id' => $this->user('id'),
            ],
        ], (24 * 60 * 60 * 365.25));
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

        /**
         * Fetch user from correct entity?
         */
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

        /**
         * Cookie should be set for non-api requests.
         */
        $this->setSecureCookie('pckg_auth_provider_' . $providerKey, [
            "user" => $user->id,
            "hash" => $sessionHash,
            "date" => date('Y-m-d H:i:s'),
        ], (24 * 60 * 60 * 365.25));

        trigger(Auth::class . '.userLoggedIn', [$user]);

        return true;
    }

    public function logout()
    {
        $providerKeys = array_keys($_SESSION['Pckg']['Auth']['Provider'] ?? []);
        unset($_SESSION['Pckg']['Auth']['Provider']);
        $this->regenerateSession();

        foreach ($providerKeys as $providerKey) {
            $this->setSecureCookie('pckg_auth_provider_' . $providerKey, null);
        }

        if ($this->getSecureCookie('pckg_auth_parentlogin')) {
            $this->performParentLogin();
            $this->setSecureCookie('pckg_auth_parentlogin', null);
        } else {
            $this->setSecureCookie('pckg_auth_autologin', null);
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
        if ($this->provider) {
            return;
        }
        
        $this->useProvider(array_keys($_SESSION['Pckg']['Auth']['Provider'] ?? [])[0] ?? 'frontend');
    }

    /**
     * @param bool $loggedIn
     * @return $this
     */
    public function setLoggedIn(bool $loggedIn = true)
    {
        $this->loggedIn = $loggedIn;

        return $this;
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
        $cookie = $this->getSecureCookie($cookieKey);
        if (!$cookie) {
            return false;
        }

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
            $this->user = $this->getProvider()->getUserById($sessionProvider['user']['id']);
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
