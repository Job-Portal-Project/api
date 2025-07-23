<?php

namespace App\Models\JWT;

use App\Enums\JWT\TokenType;
use App\Models\miexed;
use App\Models\TokenCast;
use App\Models\TokenServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    protected $table = 'jwt_tokens';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'token',
        'tokenable_id',
        'tokenable_type',
    ];

    protected $hidden = [
        'tokenable_id',
        'tokenable_type',
    ];

    protected function casts(): array
    {
        return [
            'token' => TokenCast::class,
        ];
    }

    public function isAccessToken(): bool
    {
        return $this->tokenIs(TokenType::ACCESS);
    }

    public function isRefreshToken(): bool
    {
        return $this->tokenIs(TokenType::REFRESH);
    }

    public function tokenIs(TokenType $type): bool
    {
        return $this->token->claims()->get('typ') === $type->value;
    }

    protected function encrypt(): Attribute
    {
        return Attribute::make(
            get: fn (miexed $value, array $attributes) => app()->get(TokenServiceInterface::class)->encrypt(
                $attributes['id'],
                $attributes['tokenable_id'],
                Carbon::parse($attributes['created_at']),
                Carbon::parse($attributes['expires_at']),
                Carbon::parse($attributes['can_be_used_after']),
            )
        );
    }
}
