<?php

namespace App\Form;

use App\Entity\Exercise;
use App\Service\ExerciseApiService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ExerciseType extends AbstractType
{
    private ExerciseApiService $exerciseApiService;

    public function __construct(ExerciseApiService $exerciseApiService)
    {
        $this->exerciseApiService = $exerciseApiService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $apiExercises = $this->exerciseApiService->getAllExerciseNames(); // Assuming this method exists and returns [id => name] or similar
        $apiChoices = [];
        if (is_array($apiExercises)) {
            foreach ($apiExercises as $ex) {
                if (isset($ex['id'], $ex['name'])) {
                    $choiceLabel = $ex['name'] . (isset($ex['bodyPart']) ? ' (' . $ex['bodyPart'] . ')' : '');
                    $apiChoices[$choiceLabel] = $ex['id'];
                }
            }
        }

        $builder
            ->add('apiId', ChoiceType::class, [
                'choices' => $apiChoices,
                'label' => 'Predefined Exercise (from API)',
                'placeholder' => 'Or fill details manually below',
                'required' => false,
                'attr' => [
                    'class' => 'form-select block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50'
                ]
            ])
            ->add('name', TextType::class, [
                'required' => false,
                 'label' => 'Exercise Name',
                 'attr' => [
                    'class' => 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50'
                 ]
            ])
            ->add('target', TextType::class, [
                'required' => false,
                'label' => 'Target Muscle',
                'attr' => [
                    'class' => 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50'
                 ]
            ])
            ->add('instructions', TextareaType::class, [
                'required' => false,
                'label' => 'Instructions',
                'attr' => [
                    'rows' => 5,
                    'class' => 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50'
                ]
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Upload Image (optional)',
                'mapped' => false, // Not mapped to Exercise entity directly, handled in controller
                'required' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, GIF).'
                    ])
                ],
                'attr' => [
                    'class' => 'mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Exercise::class,
        ]);
    }
}
