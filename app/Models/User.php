<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use App\Traits\HasJWTToken;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasJWTToken, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getRoleAttribute(): \Spatie\Permission\Models\Role
    {
        return $this->roles()->first();
    }

    public function profile(Role $role)
    {
        $roles = $this->getAttribute('roles')->pluck('name')->toArray();

        foreach ($roles as $name) {
            if ($role->value === $name) {
                return $this->{$name}()->first();
            }
        }

        throw new \LogicException("The user must have one of these roles: " . implode(', ', Role::values()));
    }

    public function candidate(): HasOne
    {
        if (!$this->hasRole(Role::CANDIDATE->value)) {
            throw new \LogicException("The user is not a candidate: {$this->getKey()}");
        }

        return $this->hasOne(Candidate::class);
    }

    public function company(): HasOne
    {
        if (!$this->hasRole(Role::COMPANY->value)) {
            throw new \LogicException("The user is not a company: {$this->getKey()}");
        }

        return $this->hasOne(Company::class);
    }

    public function moderator(): HasOne
    {
        if (!$this->hasRole(Role::MODERATOR->value)) {
            throw new \LogicException("The user is not a moderator: {$this->getKey()}");
        }

        return $this->hasOne(Moderator::class);
    }

    public function admin(): HasOne
    {
        if (!$this->hasRole(Role::ADMIN->value)) {
            throw new \LogicException("The user is not an admin: {$this->getKey()}");
        }

        return $this->hasOne(Admin::class);
    }
}
