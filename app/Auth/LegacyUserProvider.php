<?php

namespace App\Auth;

use App\Models\Legacy\SegUsuario;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

/**
 * Replica exactamente bo_seg_usuario::validar() del sistema legacy:
 *   - busca por cc_user (comparación sensible a mayúsculas, igual que el SQL original)
 *   - exige cfl_acceso='1' y cfl_vigencia='1'
 *   - la clave legacy es SHA-1 sin salt
 *
 * Además, y esto NO existía en el sistema legacy, hace un upgrade transparente:
 * si el login es válido y la clave todavía está en SHA-1, la reemplaza por un hash
 * bcrypt (vía Hash::make) para que a partir de ese momento use el estándar de Laravel.
 * Así los 7 usuarios actuales seleccionan su clave real la primera vez que entran,
 * sin necesidad de un reseteo masivo.
 */
class LegacyUserProvider implements UserProvider
{
    public function retrieveById($identifier): ?Authenticatable
    {
        return SegUsuario::find($identifier);
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return null; // "recordarme" no existe en el sistema legacy.
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // no-op a propósito.
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (! isset($credentials['cc_user'])) {
            return null;
        }

        return SegUsuario::query()
            ->where('cc_user', $credentials['cc_user'])
            ->where('cfl_acceso', '1')
            ->where('cfl_vigencia', '1')
            ->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $claveIngresada = $credentials['ct_clave'] ?? '';
        $hashGuardado = $user->getAuthPassword();

        if ($this->esHashLegacySha1($hashGuardado)) {
            $valido = sha1($claveIngresada) === $hashGuardado;

            if ($valido) {
                $user->actualizarHashClave(Hash::make($claveIngresada));
            }

            return $valido;
        }

        return Hash::check($claveIngresada, $hashGuardado);
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // El upgrade ya ocurre dentro de validateCredentials(); no hay nada más que hacer aquí.
    }

    private function esHashLegacySha1(string $hash): bool
    {
        return (bool) preg_match('/^[a-f0-9]{40}$/i', $hash);
    }
}
