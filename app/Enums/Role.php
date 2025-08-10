<?php

namespace App\Enums;

use Kongulov\Traits\InteractWithEnum;

enum Role: string
{
    use InteractWithEnum;

    case ADMIN = 'admin';
    case MODERATOR = 'moderator';
    case COMPANY = 'company';
    case CANDIDATE = 'candidate';
}
