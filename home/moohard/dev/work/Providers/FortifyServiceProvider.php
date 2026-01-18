<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController;
use Laravel\Fortify\Http\Controllers\ConfirmedPasswordStatusController;
use Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationPromptController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\ProfileInformationController;
use Laravel\Fortify\Http\Controllers RecoveryCodeController;
use Laravel\Fortify\Http\Controllers\RegisterController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\TwoFactorqrCodeController;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;
use Laravel\Fortify\Http\Controllers\VerifyIdColumnController;
use Laravel\Fortify\Responses\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Responses\FailedPasswordResetRequestResponse;
use Laravel\Fortify\Responses\LoginResponse;
use Laravel\Fortify\Responses\LogoutResponse;
use Laravel\Fortify\Responses\PasswordResetLinkResponse;
use Laravel\Fortify\Responses\ResetPasswordCompleteResponse;
use Laravel\Fortify\Responses\ResetPasswordResponse;
use Laravel\Fortify\Responses\SuccessfulPasswordResetLinkRequestResponse;
use Laravel\Fortify\Responses\SuccessfulPasswordResetRequestResponse;
use Laravel\Fortify\Responses\TwoFactorAuthenticationResponse;
use Laravel\Fortify\Responses\VerifyEmailResponse;
use Laravel\Fortify\Responses\ViewResponse;
use Laravel\Fortify\Rules\Password;

class FortifyServiceProvider extends \Laravel\Fortify\FortifyServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        parent::register();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePublishing();

        $this->configureRoutes();

        // View Composers...
        view()->composer('auth.login', fn ($view) => $view->with('status', session('status')));

        Fortify::ignoreRoutes();
    }

    /**
     * Configure the publishable resources offered by Fortify.
     */
    protected function configurePublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes(
            [
                __DIR__.'/../../stubs/fortify.php' => base_path('config/fortify.php'),
            ],
            'laravel-fortify-config'
        );

        $this->publishes(
            [
                __DIR__.'/../../stubs/FortifyServiceProvider.php' => base_path('app/Providers/FortifyServiceProvider.php'),
            ],
            'laravel-fortify-provider'
        );

        $this->publishes(
            [
                __DIR__.'/../../stubs/routes/web.php' => base_path('routes/fortify.php'),
            ],
            'laravel-fortify-routes'
        );
    }

    /**
     * Configure the routes offered by the application.
     */
    protected function configureRoutes(): void
    {
        if (! Fortify::$registersRoutes || $this->app->routesAreCached()) {
            return;
        }

        $this->app['router']
            ->group(
                [
                    'namespace' => 'Laravel\Fortify\Http\Controllers',
                ],
                fn ($router) => require __DIR__.'/../../stubs/routes/web.php'
            );
    }
}
