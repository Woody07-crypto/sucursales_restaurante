<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'sucursal_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_GERENTE_GENERAL = 'gerente_general';

    public const ROLE_GERENTE_SUCURSAL = 'gerente_sucursal';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function isGerenteGeneral(): bool
    {
        return $this->role === self::ROLE_GERENTE_GENERAL;
    }

    public function isGerenteSucursal(): bool
    {
        return $this->role === self::ROLE_GERENTE_SUCURSAL;
    }

    public function canAccessSucursal(Sucursal $sucursal): bool
    {
        if ($this->isGerenteGeneral()) {
            return true;
        }

        if ($this->isGerenteSucursal() && $this->sucursal_id !== null) {
            return (int) $this->sucursal_id === (int) $sucursal->id;
        }

        return false;
    }
}
