<?php

namespace App\Form;

use App\Entity\Facility;
use App\Entity\Team;
use App\Entity\Training;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TrainingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('startTime', null, [
                'widget' => 'single_text',
            ])
            ->add('endTime', null, [
                'widget' => 'single_text',
            ])
            ->add('facility', EntityType::class, [
                'class' => Facility::class,
                'choice_label' => 'name',
            ])
            ->add('coach', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email', // Using email as per User entity review
            ])
            ->add('team', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name',
            ])
            ->add('trainingExercises', CollectionType::class, [
                'entry_type' => TrainingExerciseType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false, // Important to trigger add/remove methods on Training entity
                'label' => 'Exercises', // Keep a general label for the fieldset
                'attr' => [
                    'class' => 'training-exercises-collection', // For JS targeting
                ],
                'prototype_name' => '__name__', // Ensure prototype name is set
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Training::class,
        ]);
    }
}
