<?php

namespace App\Enum;

enum OldReadNotificationTerms: int
{
    /**
     * Enum values
     *
     * Old read notification terms - the number of days after which the notification will be deleted
     */
    case NEVER = 0; // never delete
    case ONE_DAY = 1;
    case ONE_WEEK = 7;
    case TWO_WEEKS = 14;
    case ONE_MONTH = 30;
    case THREE_MONTHS = 90;

    /**
     * Auxiliary method for displaying in a drop-down list
     */
    public static function labels(): array
    {
        return [
            self::NEVER->value => 'Never',
            self::ONE_DAY->value => '1 day',
            self::ONE_WEEK->value => '1 week',
            self::TWO_WEEKS->value => '2 weeks',
            self::ONE_MONTH->value => '1 month',
            self::THREE_MONTHS->value => '3 months',
        ];
    }
}
