<?php

namespace App\Providers;

use App\AppSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            if (!Schema::hasTable('app_settings')) {
                return;
            }

            $settings = AppSetting::allAsMap();
            $company = [
                'name' => $settings['company_name'] ?? env('COMPANY_NAME', config('app.name')),
                'address' => $settings['company_address'] ?? env('COMPANY_ADDRESS'),
                'phone' => $settings['company_phone'] ?? env('COMPANY_PHONE'),
                'email' => $settings['company_email'] ?? env('COMPANY_EMAIL'),
                'ice' => $settings['company_ice'] ?? env('COMPANY_ICE'),
                'rc' => $settings['company_rc'] ?? env('COMPANY_RC'),
                'if' => $settings['company_if'] ?? env('COMPANY_IF'),
                'cnss' => $settings['company_cnss'] ?? env('COMPANY_CNSS'),
                'logo' => $settings['company_logo'] ?? env('COMPANY_LOGO', 'logo.png'),
            ];

            if (!empty($company['name'])) {
                config(['app.name' => $company['name']]);
            }

            View::share('appSettings', $settings);
            View::share('companySettings', $company);
        } catch (\Throwable $e) {
            // Ignore boot errors from settings lookup to keep app available.
        }
    }
}
