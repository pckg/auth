<?php

namespace Pckg\Auth\Middleware;

use Exception;
use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Concept\Reflect;

/**
 * Class RestrictAccess
 *
 * @package Pckg\Auth\Middleware
 */
class RestrictAccess extends AbstractChainOfReponsibility
{

    /**
     * @param  callable $next
     * @return mixed
     * @throws Exception
     */
    public function execute(callable $next)
    {
        if (isConsole()) {
            return $next();
        }

        $route = router()->get();
        $tags = config('pckg.auth.tags', []);

        if (!isset($route['tags'])) {
            return $next();
        }

        $gates = config('pckg.auth.gates', []);
        if (!$gates) {
            message('No auth gates defined.');
            return $next();
        }

        $processUnauthenticated = function ($gate, $auth) {
            $request = request();

            if ($request->isJson() || $request->isAjax() || $request->isCORS()) {
                response()->{auth()->isLoggedIn() ? 'forbidden' : 'unauthorized'}();
            }

            $redir = !$auth || !$auth->isLoggedIn() ? '?loginredirect=' . get('loginredirect', router()->getURL()) : '';
            $url = ($gate['redirect'] ?? $gate['internal']) . $redir;
            redirect($url);
            die(); // for each case?
        };

        foreach ($route['tags'] ?? [] as $tag) {
            if (is_bool($tag) && $tag) {
                continue;
            }

            foreach ($gates as $gate) {
                /**
                 * Check if gate is responsible for a tag.
                 */
                if (!in_array($tag, $gate['tags'])) {
                    continue;
                }

                $auth = auth();
                if (!$auth->canUseProvider($gate['provider'] ?? null)) {
                    continue;
                }

                $auth->useProvider($gate['provider'] ?? null);

                if (!array_key_exists($tag, $tags)) {
                    throw new Exception('Auth tag ' . $tag . ' not set.');
                }

                if (!Reflect::call($tags[$tag], [$auth])) {
                    $processUnauthenticated($gate, $auth);
                }
            }
        }

        return $next();
    }
}
