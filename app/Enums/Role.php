<?php

namespace App\Enums;

enum Role: string
{
    case USER = 'user';
    case ADMIN = 'admin';
    case INSTRUCTOR = 'instructor';
    case TEACHER = 'teacher';
}
