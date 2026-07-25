<?php

namespace App\Enum;

enum UserRole: string
{
    case ADMIN = 'admin';
    case MODERATOR = 'moderator';
    case AUTHOR = 'author';
    case USER = 'user';
}
