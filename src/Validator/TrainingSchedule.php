<?php
// src/Validator/TrainingSchedule.php
namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS)]
class TrainingSchedule extends Constraint
{
    public string $message = 'This training conflicts with existing sessions: {{ conflicts }}';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}