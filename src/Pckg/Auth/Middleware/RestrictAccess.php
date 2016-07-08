<?php namespace Pckg\Auth\Middleware;

use Pckg\Concept\AbstractChainOfReponsibility;

class RestrictAccess extends AbstractChainOfReponsibility
{

    public function execute(callable $next)
    {
        $routeName = router()->get('name');

        foreach (conf('defaults.pckg.auth', []) as $gate) {
            /**
             * Check rules for logged-out users.
             */
            if ($gate['status'] == 'logged-out' && !auth()->isLoggedIn()) {
                /**
                 * Check if route is excluded in rule.
                 */
                if (isset($gate['exclude']) && !in_array($routeName, $gate['exclude'])) {
                    redirect(url($gate['redirect']));
                }

                /**
                 * Check if route is included in rule.
                 */
                if (isset($gate['include']) && in_array($routeName, $gate['include'])) {
                    redirect(url($gate['redirect']));
                }
            }

            /**
             * Check rules for logged-in users.
             */
            if ($gate['status'] == 'logged-in' && auth()->isLoggedIn()) {
                /**
                 * Check if route is excluded in rule.
                 */
                if (isset($gate['exclude']) && !in_array($routeName, $gate['exclude'])) {
                    redirect(url($gate['redirect']));
                }

                /**
                 * Check if route is included in rule.
                 */
                if (isset($gate['include']) && in_array($routeName, $gate['include'])) {
                    redirect(url($gate['redirect']));
                }
            }
        }

        return $next();
    }

}