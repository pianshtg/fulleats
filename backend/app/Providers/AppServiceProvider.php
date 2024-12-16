<?php

namespace App\Providers;

use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\JwtCheck;
use App\Http\Middleware\JwtParse;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $namespace = 'App\\Http\\Controllers';  // Controller namespace

    public function boot()
    {
        parent::boot();
        $this->app['router']->aliasMiddleware('auth.jwt', AuthMiddleware::class);
        $this->app['router']->aliasMiddleware('jwt.check', JwtCheck::class);
        $this->app['router']->aliasMiddleware('jwt.parse', JwtParse::class);
    }

    public function map()
    {
        $this->mapApiRoutes();  // Mapping the API routes
        $this->mapWebRoutes();  // Mapping the Web routes
    }

    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)  // This is where $namespace is used
            ->group(base_path('routes/api.php'));
    }

    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }
}
