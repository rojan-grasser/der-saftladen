<?php

namespace App\Enums;

enum PostReactionType: string
{
    case LIKE = 'like';
    case DISLIKE = 'dislike';
}
