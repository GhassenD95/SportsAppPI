<?php

// src/Validator/TrainingScheduleValidator.php
namespace App\Validator;

use App\Entity\Training;
use App\Repository\TrainingRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TrainingScheduleValidator extends ConstraintValidator
{
    public function __construct(
        private TrainingRepository $trainingRepository
    ) {}

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof Training) {
            return;
        }

        $conflicts = $this->trainingRepository->findOverlappingTrainings($value);

        if (count($conflicts) > 0) {
            $conflictMessages = [];

            foreach ($conflicts as $conflict) {
                $conflictMessages[] = sprintf(
                    "%s (Team: %s, %s to %s)",
                    $conflict->getTitle(),
                    $conflict->getTeam()->getName(),
                    $conflict->getStartTime()->format('Y-m-d H:i'),
                    $conflict->getEndTime()->format('H:i')
                );
            }

            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ conflicts }}', implode(', ', $conflictMessages))
                ->atPath('startTime')
                ->addViolation();
        }
    }
}