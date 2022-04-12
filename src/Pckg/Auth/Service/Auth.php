<?php

namespace Pckg\Auth\Service;

use http\Exception;
use Pckg\Auth\Entity\Users;
use Pckg\Auth\Record\User;
use Pckg\Concept\Reflect;
use Pckg\Framework\Request\Data\SessionDriver\FileDriver;

/**
 * Class Auth
 *
 * @package Pckg\Auth\Service
 */
class Auth
{

    /**
     * @var
     */
    public $users;

    /**
     * @var
     */
    public $statuses;

    /**
     * @var
     */
    protected $provider;

    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @var bool
     */
    protected $loggedIn = false;

    /**
     * @var User
     */
    protected $user;

    protected $secureCookiePrefix = null;

    /**
     *
     */
    const COOKIE_AUTOLOGIN = 'pckgauthv2auto';

    /**
     *
     */
    const COOKIE_PARENT = 'pckgauthv2parent';

    /**
     *
     */
    const COOKIE_PROVIDER = 'pckgauthv2pro';

    /**
     *
     */
    const GEN_ALPHA = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     *
     */
    const GEN_ALPHANUM = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     *
     */
    const GEN_NUM = '0123456789';

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

    /**
     * @return int|string|null
     */
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
        //if ($this->user) {
        return $this->user;
        //}

        return $this->user = $this->getProvider()->getUserById($this->user('id'));
    }

    /**
     * @return array
     */
    public function getUserDataArray()
    {
        $user = $this->getUser();
        $data = $user ? $user->jsonSerialize() : [];
        $data['tags'] = collect(config('pckg.auth.tags', []))->map(
            function ($callable, $tag) {
                if (!Reflect::call($callable)) {
                    return;
                }

                return $tag;
            }
        )->removeEmpty()->values();

        /**
         * We want to enrich our user with some custom values.
         */
        trigger(
            Auth::class . '.getUserDataArray',
            ['user' => $user, 'data' => $data, 'setter' => function ($newData) use (&$data) {
                foreach ($newData as $key => $val) {
                    $data[$key] = $val;
                }
            }]
        );

        return $data;
    }

    /**
     * @return bool
     */
    public function is()
    {
        return !!$this->getUser();
    }

    /**
     * @param  null $key
     * @return array|mixed|null
     */
    public function user(?string $key = null)
    {
        if (!$key) {
            return $this->getUser();
        }

        return $this->getUser()->{$key} ?? null;
        $sessionUser = $this->getSessionProvider()['user'] ?? [];

        if (!$sessionUser) {
            return null;
        } elseif (!$key) {
            return $sessionUser;
        }

        return array_key_exists($key, $sessionUser) ? $sessionUser[$key] : null;
    }

    /**
     * @param  $hashedPassword
     * @param  $password
     * @return bool
     */
    public function hashedPasswordMatches($hashedPassword, $password)
    {
        $hash = config('pckg.auth.providers.' . $this->getProviderKey() . '.hash');

        return password_verify($password, $hashedPassword) || sha1($password . $hash) === $hashedPassword;
    }

    /**
     * @param  $password
     * @return false|string|null
     */
    public function hashPassword($password)
    {
        $version = config('pckg.auth.providers.' . $this->getProviderKey() . '.version');
        $hash = config('pckg.auth.providers.' . $this->getProviderKey() . '.hash');

        /**
         * @T00D00 ... add salt to password
         */
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * @param  int    $length
     * @param  string $chars
     * @return string
     */
    public function createPassword($length = 10, $chars = self::GEN_ALPHANUM)
    {
        $characters = str_split($chars, 1);
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[array_rand($characters)];
        }

        return $password;
    }

    /**
     * @param  $email
     * @param  $password
     * @return bool
     */
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

    /**
     * @param  $id
     * @return bool
     */
    public function autologin($id)
    {
        $rUser = $this->getProvider()->getUserById($id);

        if ($rUser) {
            return $this->performLogin($rUser);
        }

        return false;
    }

    /**
     * @return array|callable|mixed|\Pckg\Framework\Config|null
     */
    public function getSecurityHash()
    {
        $hash = config('security.hash', null);

        if (dev() && strlen($hash) < 64) {
            message('Make sure security hash is set (min length 64, current length ' . strlen($hash) . '!');
        }

        return $hash;
    }

    public function getPrefixedCookieName(string $name)
    {
        /**
         * We DO want to use store identifier in cookie name, because multi-tenancy.
         * And we DO want to use that on our session provider.
         */
        $prefix = $this->getSecureCookiePrefix();
        if (!$prefix && !is_string($prefix)) {
            $prefix = config('pckg.auth.cookiePrefix', null);
        }

        return [$prefix . $name, $prefix];
    }

    public function setSecureCookiePrefix(?string $prefix)
    {
        $this->secureCookiePrefix = $prefix;

        return $this;
    }

    public function getSecureCookiePrefix()
    {
        return $this->secureCookiePrefix;
    }

    /**
     * Decode cookie with value, signature and host values.
     *
     * @param  string $name
     * @return mixed|null
     */
    public function getSecureCookie(string $name)
    {
        [$name, $prefix] = $this->getPrefixedCookieName($name);
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
        if (!$this->hashedPasswordMatches($decoded['signature'], $decoded['value'] . $decoded['host'] . $name . $prefix)) {
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
    public function setSecureCookie(string $name, $value = null, $duration = null)
    {
        [$name, $prefix] = $this->getPrefixedCookieName($name);

        /**
         * Delete cookie when empty value or negative duration.
         */
        if (!$value || !$duration || $duration < 0) {
            cookie()->set($name, null, (-24 * 60 * 60 * 365.25));
            return;
        }

        /**
         * Encode cookie elsewise.
         */
        $host = server('HTTP_HOST', null);
        $encoded = base64_encode(json_encode($value));
        $signature = $this->hashPassword($encoded . $host . $name . $prefix);
        $value = base64_encode(
            json_encode(
                [
                    'value' => $encoded,
                    'signature' => $signature,
                    'host' => $host,
                ]
            )
        );

        cookie()->set($name, $value, $duration);
    }

    public function setParentLogin()
    {
        $this->setSecureCookie(
            static::COOKIE_PARENT,
            [
            $this->getProviderKey() => [
                'hash' => password_hash(
                    $this->getSecurityHash() .
                    $this->user('id') .
                    $this->user('autologin'),
                    PASSWORD_DEFAULT
                ),
                'user_id' => $this->user('id'),
            ],
            ],
            (60 * 60)
        );
    }

    public function performAutologin()
    {
        $cookie = $this->getSecureCookie(static::COOKIE_AUTOLOGIN);
        if (!$cookie) {
            return;
        }
        return $this->performLoginFromStorage($cookie);
    }

    public function performParentLogin()
    {
        $cookie = $this->getSecureCookie(static::COOKIE_PARENT);
        if (!$cookie) {
            return;
        }
        $this->performLoginFromStorage($cookie);
    }

    public function canUseProvider(string $provider)
    {
        $authConfig = config('pckg.auth.providers.' . $provider);

        return $authConfig && !isset($authConfig['disabled']);
    }

    /**
     * @param  array $storage
     * @return bool
     * @throws \Exception
     */
    protected function performLoginFromStorage(array $storage)
    {
        foreach ($storage as $provider => $data) {
            // new
            if ($provider !== $this->getProviderKey()) {
                continue;
            }

            if (!is_array($data)) {
                continue;
            }

            $userId = $data['user_id'] ?? null;
            $hash = $data['hash'] ?? null;

            if (!$userId || !$hash) {
                continue;
            }

            // skip disabled providers
            if (!$this->canUseProvider($provider)) {
                continue;
            }

            // $this->useProvider($provider);

            $user = $this->getProvider()->getUserById($userId);

            if (!$user) {
                continue;
            }

            $entry = $this->getSecurityHash() . $user->id . $user->autologin;

            if (!password_verify($entry, $hash)) {
                // changing the autologin invalidates sessions
                //throw new \Exception('Not verified cookie!');
                // throw error?
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
        $original = $this->getSecurityHash() . $this->user('id') . $this->user('autologin');
        $this->setSecureCookie(
            static::COOKIE_AUTOLOGIN,
            [
            $this->getProviderKey() => [
                'hash' => password_hash($original, PASSWORD_DEFAULT),
                'user_id' => $this->user('id'),
            ],
            ],
            (24 * 60 * 60 * 365.25)
        );
    }

    /**
     * @param $user
     */
    public function authenticate($user)
    {
        $providerKey = $this->getProviderKey();
        $sessionHash = password_hash($this->getSecurityHash() . session_id(), PASSWORD_DEFAULT);

        $_SESSION['Pckg']['Auth']['Provider'] = [
            $providerKey => [
                "user" => $user ? $user->toArray() : [],
                "hash" => $sessionHash,
                "flags" => [],
            ]
        ];

        $this->loggedIn = true;
        $this->user = $user;

        if ($user) {
            trigger(Auth::class . '.userLoggedIn', [$user]);
        }
    }

    /**
     * @param  $user
     * @return string
     */
    public function getUserSecuritySessionPass($user)
    {
        return $this->getSecurityHash() . '_' . $user->id . '_' . session_id();
    }

    public function regenerateSession()
    {
        session()->getDriver()->regenerate();
    }

    /**
     * @param  $user
     * @return bool
     */
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
            "user" => $user->toArray(),
            "hash" => $sessionHash,
            "date" => date('Y-m-d H:i:s'),
            "flags" => [],
        ];

        /**
         * Cookie should be set for non-api requests.
         */
        $this->setSecureCookie(
            static::COOKIE_PROVIDER . '_' . $providerKey,
            [
            "user" => $user->id,
            "hash" => $sessionHash,
            "date" => date('Y-m-d H:i:s'),
            ],
            (24 * 60 * 60 * 365.25)
        );

        trigger(Auth::class . '.userLoggedIn', [$user]);

        return true;
    }

    public function logout()
    {
        $providerKeys = array_keys($_SESSION['Pckg']['Auth']['Provider'] ?? []);
        unset($_SESSION['Pckg']['Auth']['Provider']);
        $this->regenerateSession();

        foreach ($providerKeys as $providerKey) {
            $this->setSecureCookie(static::COOKIE_PROVIDER . '_' . $providerKey, null);
        }

        if ($this->getSecureCookie(static::COOKIE_PARENT)) {
            $this->performParentLogin();
            $this->setSecureCookie(static::COOKIE_PARENT, null);
        } else {
            $this->setSecureCookie(static::COOKIE_AUTOLOGIN, null);
        }

        $this->loggedIn = false;
        $this->user = null;
    }

    /**
     * @return array|mixed
     */
    public function getSessionProvider()
    {
        return $_SESSION['Pckg']['Auth']['Provider'][$this->getProviderKey()] ?? [];
    }

    public function requestProvider()
    {
        if ($this->provider) {
            return;
        }

        /**
         * Authenticate with last authenticated method from session?
         */
        $this->useProvider(array_keys($_SESSION['Pckg']['Auth']['Provider'] ?? [])[0] ?? 'frontend');
    }

    /**
     * @param  bool $loggedIn
     * @return $this
     */
    public function setLoggedIn(bool $loggedIn = true)
    {
        $this->loggedIn = $loggedIn;

        return $this;
    }

    /**
     * @param  null $user
     * @return $this
     */
    public function setUser($user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->loggedIn;
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return !$this->isLoggedIn();
    }

    /**
     * @return bool
     */
    public function isSuperadmin()
    {
        $is = $this->isLoggedIn() && method_exists($this->getUser(), 'isSuperadmin') && $this->getUser()->isSuperadmin();

        return $is;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        $is = $this->isLoggedIn() && method_exists($this->getUser(), 'isAdmin') && $this->getUser()->isAdmin();

        return $is;
    }

    /**
     * @return bool
     */
    public function isCheckin()
    {
        return $this->isLoggedIn() && method_exists($this->getUser(), 'isCheckin') && $this->getUser()->isCheckin();
    }

    /**
     * @return mixed|null
     */
    public function getGroupId()
    {
        $groupKey = config('pckg.auth.providers.' . $this->getProviderKey() . '.userGroup', 'user_group_id');

        return $this->user($groupKey) ?? null;
    }

    /**
     * @param  $email
     * @return bool
     */
    public function isEmail($email)
    {
        if (!is_array($email)) {
            $email = [$email];
        }

        return in_array($this->user('email'), $email);
    }

    /**
     * @param  null $url
     * @return string
     */
    public function getNewInternalGetParameter($url = null)
    {
        $data = [
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        $data['hash'] = auth()->hashPassword(config('identifier', null) . $data['timestamp'] . $this->getSecurityHash() . $url);

        if ($url) {
            $data['url'] = true;
        }

        $data['signature'] = sha1(json_encode($data));

        return base64_encode(json_encode($data));
    }

    /**
     * @param  string $internal
     * @return bool
     */
    public function isValidInternalGetParameter(string $internal)
    {
        try {
            /**
             * We will generate password on request
             */
            $decoded = json_decode(base64_decode($internal), true);

            /**
             * Validate request first.
             */
            $signature = $decoded['signature'];
            unset($decoded['signature']);
            if ($signature !== sha1(json_encode($decoded))) {
                return false;
            }

            /**
             * Validate timestamp.
             */
            $timestamp = $decoded['timestamp'];
            if (strtotime($timestamp) < strtotime('-3hours')) {
                return false;
            }

            /**
             * Validate hash.
             */
            $hash = $decoded['hash'];
            $identifier = config('identifier', null);
            if ($this->hashedPasswordMatches($hash, $identifier . $timestamp . $this->getSecurityHash() . (isset($decoded['url']) ? request()->getUrl(true) : null))) {
                return true;
            }
        } catch (\Throwable $e) {
            error_log(exception($e));
        }

        return false;
    }
}
