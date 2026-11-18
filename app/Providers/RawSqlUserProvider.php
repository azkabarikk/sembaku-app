<?php

namespace App\Providers;

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use SensitiveParameter;

class RawSqlUserProvider implements UserProvider
{
    public function retrieveById(mixed $identifier): ?Authenticatable
    {
        $user = DB::selectOne(
            'SELECT iduser AS id, username, idrole, nama_role FROM v_master_user WHERE iduser = ?',
            [$identifier]
        );

        return $user ? new GenericUser((array) $user + ['remember_token' => null]) : null;
    }

    public function retrieveByToken(mixed $identifier, #[SensitiveParameter] mixed $token): ?Authenticatable
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, #[SensitiveParameter] mixed $token): void {}

    public function retrieveByCredentials(#[SensitiveParameter] array $credentials): ?Authenticatable
    {
        $username = $credentials['username'] ?? null;

        if (! is_string($username)) {
            return null;
        }

        $user = DB::selectOne(
            'SELECT u.iduser AS id, u.username, u.password, r.idrole, r.nama_role
             FROM `user` u JOIN `role` r ON r.idrole = u.idrole WHERE u.username = ?',
            [$username]
        );

        return $user ? new GenericUser((array) $user + ['remember_token' => null]) : null;
    }

    public function validateCredentials(Authenticatable $user, #[SensitiveParameter] array $credentials): bool
    {
        $password = $credentials['password'] ?? null;
        $hash = $user->getAuthPassword();

        if (! is_string($password) || ! is_string($hash)) {
            return false;
        }

        if ($this->passwordLama($hash)) {
            return hash_equals($hash, $password);
        }

        try {
            return Hash::check($password, $hash);
        } catch (RuntimeException) {
            return false;
        }
    }

    public function rehashPasswordIfRequired(Authenticatable $user, #[SensitiveParameter] array $credentials, bool $force = false): void
    {
        $tersimpan = $user->getAuthPassword();
        $password = $credentials['password'] ?? null;

        if (! is_string($tersimpan) || ! is_string($password) || ! $this->passwordLama($tersimpan)) {
            return;
        }

        DB::update(
            'UPDATE `user` SET password = ? WHERE iduser = ? AND SHA2(password, 256) = ?',
            [Hash::make($password), $user->getAuthIdentifier(), hash('sha256', $tersimpan)]
        );
    }

    private function passwordLama(string $password): bool
    {
        return $password !== '' && strlen($password) < 32 && ! str_starts_with($password, '$');
    }
}
