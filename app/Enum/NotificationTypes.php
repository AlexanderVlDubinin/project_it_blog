<?php

namespace App\Enum;

enum NotificationTypes: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case DANGER = 'danger';
    case SUCCESS = 'success';
    //case GRAY = 'gray';

    public static function labels(): array
    {
        return [
            self::INFO->value => 'Info (Blue color)',
            self::WARNING->value => 'Warning (Yellow color)',
            self::DANGER->value => 'Danger (Red color)',
            self::SUCCESS->value => 'Success (Green color)',
            //self::GRAY->value => 'Neutral (Gray color)',
        ];
    }
}
