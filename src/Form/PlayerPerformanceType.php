<?php

namespace App\Form;

use App\Entity\PlayerPerformance;
use App\Entity\Team;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlayerPerformanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('performanceDate', null, [
                'widget' => 'single_text',
            ])
            ->add('goalsScored')
            ->add('assists')
            ->add('minutesPlayed')
            ->add('shotsOnTarget')
            ->add('passesCompleted')
            ->add('tackles')
            ->add('interceptions')
            ->add('saves')
            ->add('rating')
            ->add('notes')
            ->add('player', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('team', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlayerPerformance::class,
        ]);
    }
}
