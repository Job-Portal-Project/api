<?php

namespace App\Enums\JWT;

use Kongulov\Traits\InteractWithEnum;

enum TokenType: string
{
	use InteractWithEnum;
	
	case ACCESS = 'access';
	case REFRESH = 'refresh';
}

