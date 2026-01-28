<?php

namespace App\Enums;

enum PostReaction: string
{
    case LIKE = 'like';
    case DISLIKE = 'dislike';
}
