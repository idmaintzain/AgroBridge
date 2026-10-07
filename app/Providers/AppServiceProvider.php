<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('pagination.default');
        Paginator::defaultSimpleView('pagination.default');

        Blade::directive('naira', function (string $expression) {
            return "<?php echo e(\\App\\Support\\Money::naira((int) {$expression})); ?>";
        });
    }
}
