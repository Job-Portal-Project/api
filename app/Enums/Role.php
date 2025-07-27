<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'Admin';
    case MODERATOR = 'Moderator';
    case COMPANY = 'Company';
    case CANDIDATE = 'Candidate';
}
