<?php

namespace Happytodev\Blogr\Rendering\Callout;

enum CalloutType: string
{
    case Tip = 'tip';
    case Info = 'info';
    case Danger = 'danger';
    case Caution = 'caution';

    public function label(): string
    {
        return match ($this) {
            self::Tip => 'Tip',
            self::Info => 'Info',
            self::Danger => 'Danger',
            self::Caution => 'Caution',
        };
    }

    public static function tryFromStart(string $line): ?self
    {
        foreach (self::cases() as $type) {
            if (preg_match('/^:::' . $type->value . '(?:\[(.+?)\])?\s*$/', $line, $matches)) {
                return $type;
            }
        }

        return null;
    }

    public static function extractTitle(string $line): ?string
    {
        foreach (self::cases() as $type) {
            if (preg_match('/^:::' . $type->value . '\[(.+?)\]\s*$/', $line, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}
