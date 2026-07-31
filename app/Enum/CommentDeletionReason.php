<?php

namespace App\Enum;

enum CommentDeletionReason: string
{
    case SPAM = 'spam';
    case PROFANITY = 'profanity';
    case FLOOD = 'flood / offtop';
    case INSULTS = 'insults';
    case RULE_VIOLATION = 'rule_violation';
    case OTHER = 'other';

    // Вспомогательный метод для вывода в выпадающий список
    public static function labels(): array
    {
        return [
            self::SPAM->value => 'Spam / Advertising',
            self::PROFANITY->value => 'Foul language',
            self::FLOOD->value => 'Flood / Off-topic message',
            self::INSULTS->value => 'Insults / Aggression',
            self::RULE_VIOLATION->value => 'Rule violation',
            self::OTHER->value => 'Other reason (specify manually)',
        ];
    }
}
