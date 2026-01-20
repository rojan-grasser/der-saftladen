<?php

namespace App\Enums;

enum UserRole: string
{
    case USER = 'user';
    case ADMIN = 'admin';
    case INSTRUCTOR = 'instructor';
    case TEACHER = 'teacher';
}
