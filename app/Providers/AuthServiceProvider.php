<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\DailyReport;
use App\Policies\DailyReportPolicy;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        DailyReport::class => DailyReportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
         Gate::before(function ($user, $ability) {
        return $user->hasRole('Super-Administrador') ? true : null;});

        Gate::define('daily_reports.edit_all', function ($user) {
            return $user->can('daily_reports.edit_all');
        });
        
        Gate::define('daily_reports.edit', function ($user, DailyReport $dailyReport) {
            return $user->hasPermissionTo('daily_reports.edit') && $user->id === $dailyReport->user_id;
    });

    }
}
