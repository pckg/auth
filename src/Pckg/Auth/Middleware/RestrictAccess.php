<?php namespace Pckg\Auth\Middleware;

use Exception;
use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Concept\Reflect;

class RestrictAccess extends AbstractChainOfReponsibility
{

    public function execute(callable $next)
    {
        if (isConsole()) {
            return $next();
        }

        $route = router()->get();
        $tags = config('pckg.auth.tags', []);

        if (isset($route['tags'])) {
            foreach (config('pckg.auth.gates', []) as $gate) {
                $auth = auth($gate['provider']);

                /**
                 * Check for tags.
                 * All tags should return true value.
                 */
                foreach ($route['tags'] ?? [] as $tag) {
                    if (!in_array($tag, $gate['tags'])) {
                        continue;
                    }

                    if (!array_key_exists($tag, $tags)) {
                        throw new Exception('Auth tag ' . $tag . ' not set.');
                    }

                    if (!Reflect::call($tags[$tag], [$auth])) {
                        if (request()->isJson() || request()->isAjax()) {
                            response()->{auth()->isLoggedIn() ? 'forbidden' : 'unauthorized'}();
                        }

                        $redir = '?loginredirect=' . get('loginredirect', router()->getURL());
                        $url = ($gate['redirect'] ?? $gate['internal']) . $redir;
                        redirect($url);
                    }
                }
            }
        }

        return $next();
    }

}