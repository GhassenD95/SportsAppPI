<?php

// src/Enum/ExerciseIntensity.php
namespace App\Enum;

enum ExerciseIntensity: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case VERY_HIGH = 'very_high';

    public static function getChoices(): array
    {
        return [
            'Low' => self::LOW->value,
            'Medium' => self::MEDIUM->value,
            'High' => self::HIGH->value,
            'Very High' => self::VERY_HIGH->value,
        ];
    }

    public static function getFormChoices(): array
    {
        return array_flip(self::getChoices());
    }
}