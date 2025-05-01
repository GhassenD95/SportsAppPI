<?php

// src/Form/TrainingExerciseType.php
// src/Form/TrainingExerciseType.php
namespace App\Form;

use App\Entity\Exercise;
use App\Entity\TrainingExercise;
use App\Enum\ExerciseIntensity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class TrainingExerciseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('exercise', EntityType::class, [
                'class' => Exercise::class,
                'choice_label' => 'name',
                'placeholder' => 'Select an exercise',
                'constraints' => [
                    new Assert\NotNull(message: 'Please select an exercise')
                ],
                'attr' => [
                    'data-controller' => 'autocomplete',
                    'required' => true
                ]
            ])
            ->add('durationMinutes', IntegerType::class, [
                'label' => 'Duration (minutes)',
                'constraints' => [
                    new Assert\NotNull(),
                    new Assert\Positive(),
                    new Assert\LessThanOrEqual(240)
                ],
                'attr' => [
                    'min' => 1,
                    'max' => 240,
                    'required' => true
                ]
            ])
            ->add('intensity', ChoiceType::class, [ // Changed to ChoiceType
                'label' => 'Intensity Level',
                'choices' => ExerciseIntensity::getChoices(), // Using enum values
                'placeholder' => 'Select intensity level',
                'constraints' => [
                    new Assert\NotNull(),
                    new Assert\Choice(callback: [ExerciseIntensity::class, 'getChoices'])
                ],
                'attr' => ['required' => true]
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(max: 1000)
                ],
                'attr' => ['rows' => 3]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TrainingExercise::class,
            'error_mapping' => [
                '.' => 'exercise' // Maps global errors to exercise field
            ]
        ]);
    }
}