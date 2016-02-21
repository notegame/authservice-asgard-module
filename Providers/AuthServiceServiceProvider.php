<?php namespace Modules\AuthService\Providers;

use Illuminate\Support\ServiceProvider;

class AuthServiceServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * @var array
     */
    protected $middleware = [
        'AuthService' => [
            'auth.token' => 'TokenAuthenticate',
        ],
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->registerBindings();

    }

    /**
     */
    public function boot()
    {
        $this->registerMiddleware($this->app['router']);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }

    private function registerBindings()
    {
        // add bindings
    }

    private function registerMiddleware($router)
    {
        foreach ($this->middleware as $module => $middlewares) {
            foreach ($middlewares as $name => $middleware) {
                $class = "Modules\\{$module}\\Http\\Middleware\\{$middleware}";

                $router->middleware($name, $class);
            }
        }
    }
}
