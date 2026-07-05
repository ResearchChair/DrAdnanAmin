<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSync
{
    public static function sync(): void
    {
        $email = (string) config('admin_security.login_email', '');
        $password = (string) config('admin_security.password', '');
        $username = (string) config('admin_security.username', 'admin');

        if ($email === '' || $password === '') {
            return;
        }

        $user = User::query()->firstWhere('email', $email);

        if (! $user) {
            User::query()->create([
                'name' => ucfirst($username),
                'email' => $email,
                'password' => Hash::make($password),
            ]);
        } elseif (! Hash::check($password, $user->password)) {
            $user->update([
                'name' => ucfirst($username),
                'password' => Hash::make($password),
            ]);
        } elseif ($user->name !== ucfirst($username)) {
            $user->update(['name' => ucfirst($username)]);
        }

        $legacyEmail = 'admin@portfolio.local';

        if ($email !== $legacyEmail) {
            User::query()->where('email', $legacyEmail)->delete();
        }
    }
}
