<?php

namespace App\Enum;

enum Sport: string
{
    case VOLLEYBALL = 'volleyball';
    case BASKETBALL = 'basketball';
    case FOOTBALL = 'football';

    public static function choices(): array
    {
        return [
            'Volleyball' => self::VOLLEYBALL->value,
            'Basketball' => self::BASKETBALL->value,
            'Football' => self::FOOTBALL->value,
        ];
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
