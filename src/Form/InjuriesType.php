<?php

namespace App\Form;

use App\Entity\Injuries;
use App\Entity\MedicalReport;
use App\Entity\Team;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InjuriesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('injuryType')
            ->add('severity')
            ->add('injuryDate', null, [
                'widget' => 'single_text',
            ])
            ->add('expectedRecoveryDate', null, [
                'widget' => 'single_text',
            ])
            ->add('actualRecoveryDate', null, [
                'widget' => 'single_text',
            ])
            ->add('description')
            ->add('treatmentPlan')
            ->add('status')
            ->add('notes')
            ->add('player', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('medicalReport', EntityType::class, [
                'class' => MedicalReport::class,
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
            'data_class' => Injuries::class,
        ]);
    }
}
