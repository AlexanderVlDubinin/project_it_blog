<?php

namespace App\Enum;

enum CommentDeletionReason: string
{
    /**
     * Enum values
     * Reasons for deleting a comment
     */
    case SPAM = 'spam';
    case PROFANITY = 'profanity';
    case FLOOD = 'flood / offtop';
    case INSULTS = 'insults';
    case RULE_VIOLATION = 'rule_violation';
    case SELF_DELETE = 'self_delete';
    case OTHER = 'other';

    /**
     * Auxiliary method for displaying in a drop-down list
     */
    public static function labels(): array
    {
        return [
            self::SPAM->value => 'Spam / Advertising',
            self::PROFANITY->value => 'Foul language',
            self::FLOOD->value => 'Flood / Off-topic message',
            self::INSULTS->value => 'Insults / Aggression',
            self::RULE_VIOLATION->value => 'Rule violation',
            self::SELF_DELETE->value => 'Deleted by author',
            self::OTHER->value => 'Other reason (specify manually)',
        ];
    }
}
