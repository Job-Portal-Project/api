<?php

namespace App\Traits;

use App\Models\JWT\Token;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

trait HasJWTToken
{
    public function tokens(): MorphMany
    {
        return $this->morphMany(Token::class, 'tokenable');
    }

    public function newTokens(): Attribute
    {
        return Attribute::make(
            get: fn (Collection $value) => $value,
            set: fn (Collection $value) => $value,
        );
    }
}
