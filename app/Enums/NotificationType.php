<?php

namespace App\Enums;

enum NotificationType: string
{
    case NEW_COMMENT = 'new_comment';
    // Potential future types
    // case NEW_TOPIC = 'new_topic';
    // case MENTION = 'mention';

    public function label(): string
    {
        return match ($this) {
            self::NEW_COMMENT => 'Neue Kommentare zu von Ihnen Abonnierten Themen',
        };
    }
}
