<?php

namespace App\Enums;

enum NotificationType: string
{
    case NEW_COMMENT = 'new_comment';
    // Potential future types
    // case NEW_TOPIC = 'new_topic';
    // case MENTION = 'mention';
}
