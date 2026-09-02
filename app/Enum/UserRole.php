<?php

namespace App\Enum;

enum UserRole: string
{
    /**
     * Enum values
     *
     * User roles
     */
    case ADMIN = 'admin';
    case MODERATOR = 'moderator';
    case AUTHOR = 'author';
    case USER = 'user';
}
