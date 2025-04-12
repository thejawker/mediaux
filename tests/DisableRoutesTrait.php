<?php

namespace TheJawker\Mediaux\Tests;

trait DisableRoutesTrait
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function disableRoutesTrait()
    {
        dd('erlgijer');
        // Call parent if it exists
        if (method_exists(get_parent_class($this), 'getEnvironmentSetUp')) {
            parent::getEnvironmentSetUp($app);
        }

        // Disable the Mediaux routes
        $app['config']->set('mediaux.disable_routes', true);
    }
}
