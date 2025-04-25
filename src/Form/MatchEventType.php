<?php

namespace App\Form;

use App\Entity\MatchEvent;
use App\Entity\Team;
use App\Entity\Tournament;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MatchEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', null, [
                'widget' => 'single_text',
            ])
            ->add('location')
            ->add('awayTeam')
            ->add('score')
            ->add('homeTeam', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'id',
            ])
            ->add('tournament', EntityType::class, [
                'class' => Tournament::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MatchEvent::class,
        ]);
    }
}
