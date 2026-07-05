<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        $maxAttempts = config('admin_security.login_max_attempts', 5);
        $decaySeconds = config('admin_security.login_decay_seconds', 300);

        try {
            $this->rateLimit($maxAttempts, $decaySeconds);
            $this->enforceEmailRateLimit($maxAttempts, $decaySeconds);
        } catch (TooManyRequestsException $exception) {
            $this->logSuspiciousActivity('rate_limited', [
                'seconds_until_retry' => $exception->secondsUntilAvailable,
            ]);

            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->logSuspiciousActivity('failed_credentials', [
                'email' => $data['email'] ?? null,
            ]);

            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();

            $this->logSuspiciousActivity('unauthorized_panel_access', [
                'email' => $data['email'] ?? null,
                'user_id' => $user->getAuthIdentifier(),
            ]);

            $this->throwFailureValidationException();
        }

        RateLimiter::clear($this->emailRateLimitKey($data['email'] ?? ''));

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function enforceEmailRateLimit(int $maxAttempts, int $decaySeconds): void
    {
        $email = strtolower(trim((string) ($this->data['email'] ?? '')));

        if ($email === '') {
            return;
        }

        $key = $this->emailRateLimitKey($email);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $secondsUntilAvailable = RateLimiter::availableIn($key);

            throw new TooManyRequestsException(static::class, 'authenticate', request()->ip(), $secondsUntilAvailable);
        }

        RateLimiter::hit($key, $decaySeconds);
    }

    protected function emailRateLimitKey(string $email): string
    {
        return 'admin-login-email:'.sha1(strtolower(trim($email)).'|'.request()->ip());
    }

    protected function logSuspiciousActivity(string $reason, array $context = []): void
    {
        Log::warning('Admin login blocked or failed', array_merge([
            'reason' => $reason,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ], $context));
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Username')
            ->required()
            ->autocomplete('username')
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => $this->resolveLoginIdentifier((string) ($data['email'] ?? '')),
            'password' => $data['password'],
        ];
    }

    protected function resolveLoginIdentifier(string $login): string
    {
        $login = trim($login);

        if ($login === '' || str_contains($login, '@')) {
            return $login;
        }

        if (strtolower($login) === strtolower((string) config('admin_security.username', 'admin'))) {
            return (string) config('admin_security.login_email', 'admin@portfolio.local');
        }

        return $login;
    }
}
