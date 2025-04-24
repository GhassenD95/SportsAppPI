<?php

namespace App\Enum;

enum EquipmentType: string
{
    case BALL = 'ball';
    case NET = 'net';
    case GOALPOST = 'goalpost';
    case UNIFORM = 'uniform';
    case SHOES = 'shoes';
    case PROTECTIVE_GEAR = 'protective_gear';
    case TRAINING_EQUIPMENT = 'training_equipment';
    case OTHER = 'other';

    public static function choices(): array
    {
        return [
            'Ball' => self::BALL->value,
            'Net' => self::NET->value,
            'Goalpost' => self::GOALPOST->value,
            'Uniform' => self::UNIFORM->value,
            'Shoes' => self::SHOES->value,
            'Protective Gear' => self::PROTECTIVE_GEAR->value,
            'Training Equipment' => self::TRAINING_EQUIPMENT->value,
            'Other' => self::OTHER->value,
        ];
    }
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
