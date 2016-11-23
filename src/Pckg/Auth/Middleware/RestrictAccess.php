<?php namespace Pckg\Auth\Middleware;

use Pckg\Concept\AbstractChainOfReponsibility;
use Pckg\Concept\Reflect;

class RestrictAccess extends AbstractChainOfReponsibility
{

    public function execute(callable $next)
    {
        $router = router()->get();
        $routeName = $router['name'];

        foreach (config('pckg.auth.gates', []) as $gate) {
            $auth = auth($gate['provider']);

            /**
             * Check rules for logged-out users.
             */
            if ($gate['status'] == 'logged-out' && $auth->isLoggedIn()) {
                continue;

            } elseif ($gate['status'] == 'logged-in' && !$auth->isLoggedIn()) {
                continue;

            }

            /**
             * Check if route is excluded in rule.
             */
            if (isset($gate['exclude'])) {
                if (in_array($routeName, $gate['exclude'])) {
                    continue;
                }

                foreach ($gate['exclude'] as $route) {
                    if (preg_match('#' . $route . '#', $routeName)) {
                        continue;
                    }
                }
            }

            /**
             * Check if route is included in rule.
             */
            if (isset($gate['include']) && !in_array($routeName, $gate['include'])) {
                $found = false;
                foreach ($gate['include'] as $route) {
                    if (preg_match('#' . $route . '#', $routeName)) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    continue;
                }
            }

            /**
             * Check for callback.
             */
            if (isset($gate['callback'])) {
                if (Reflect::method($gate['callback']['class'], $gate['callback']['method'])) {
                    continue;
                }
            }

            redirect(url($gate['redirect']));
        }

        return $next();
    }

}