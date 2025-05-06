<?php

namespace App\Form;

use App\Entity\Facility;
use App\Enum\Sport; // Import the Sport Enum
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class FacilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('location')
            ->add('imageFile', FileType::class, [
                'label' => 'Facility Image (JPG or PNG file)',
                'mapped' => false, // Not mapped to the entity property directly
                'required' => false, // Optional image upload
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid JPG or PNG image',
                    ])
                ],
            ])
            ->add('sports', ChoiceType::class, [
                'choices' => array_combine(Sport::values(), Sport::values()), // Use Sport enum values
                'multiple' => true,
                'expanded' => false, // Set to true if you want checkboxes instead of a select
                'label' => 'Sports Offered',
                'attr' => ['class' => 'select2'], // Optional: for Select2 styling if you use it
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Facility::class,
        ]);
    }
}
