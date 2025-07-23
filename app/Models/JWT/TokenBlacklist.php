<?php

namespace App\Models\JWT;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenBlacklist extends Model
{
    use HasFactory;

    protected $table = 'jwt_token_blacklist';

    protected $fillable = [
        'jwt_token_id',
    ];
}
