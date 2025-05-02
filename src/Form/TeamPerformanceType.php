<?php

namespace App\Form;

use App\Entity\Team;
use App\Entity\TeamPerformance;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamPerformanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('performanceDate', null, [
                'widget' => 'single_text',
            ])
            ->add('goalsScored')
            ->add('goalsConceded')
            ->add('shotsOnTarget')
            ->add('shotsConceded')
            ->add('possessionPercentage')
            ->add('passesCompleted')
            ->add('tackles')
            ->add('interceptions')
            ->add('fouls')
            ->add('yellowCards')
            ->add('redCards')
            ->add('rating')
            ->add('notes')
            ->add('team', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TeamPerformance::class,
        ]);
    }
}
