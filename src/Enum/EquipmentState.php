<?php

namespace App\Enum;

enum EquipmentState: string
{
    case NEW = 'new';
    case GOOD = 'good';
    case NEEDS_REPAIR = 'needs_repair';
    case RETIRED = 'retired';

    public static function choices(): array
    {
        return array_combine(
            array_map(fn(self $state) => ucfirst(str_replace('_', ' ', $state->name)), self::cases()),
            array_column(self::cases(), 'value')
        );
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
