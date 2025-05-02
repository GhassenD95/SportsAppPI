<?php

namespace App\Controller\Admin;

use App\Entity\Training;
use App\Entity\User;
use App\Form\TrainingExerciseType;
use App\Repository\TrainingRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TrainingCrudController extends AbstractCrudController
{
    public function __construct(
        private TrainingRepository $trainingRepository
    ) {}

    public static function getEntityFqcn(): string
    {
        return Training::class;
    }

    // src/Controller/Admin/TrainingCrudController.php
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Training Session')
            ->setEntityLabelInPlural('Training Sessions')
            ->setFormOptions([
                'validation_groups' => ['Default', 'training_validation'],
                'error_mapping' => [
                    '.' => 'startTime', // Map class-level errors to startTime field
                ]
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add('index', 'detail');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('title')
                ->setFormTypeOptions([
                    'attr' => ['autofocus' => true],
                ]),
            TextEditorField::new('description')
                ->onlyOnForms()
                ->setFormTypeOptions([
                    'attr' => ['rows' => 5],
                ]),
            DateTimeField::new('startTime')
                ->setFormTypeOptions([
                    'widget' => 'single_text',
                    'html5' => true,
                    'attr' => [
                        'class' => 'datetime-picker',
                        'data-error-container' => '#startTime-errors'
                    ],
                ]),
            DateTimeField::new('endTime')
                ->setFormTypeOptions([
                    'widget' => 'single_text',
                    'html5' => true,
                    'attr' => [
                        'class' => 'datetime-picker',
                        'data-error-container' => '#endTime-errors'
                    ],
                ]),
            AssociationField::new('facility')
                ->setFormTypeOptions([
                    'attr' => [
                        'data-widget' => 'select2'
                    ]
                ]),
            AssociationField::new('coach')
                ->setQueryBuilder(
                    fn (QueryBuilder $queryBuilder) => $queryBuilder
                        ->andWhere('entity.roles LIKE :role')
                        ->setParameter('role', '%ROLE_COACH%')
                )
                ->setFormTypeOptions([
                    'attr' => [
                        'data-widget' => 'select2'
                    ]
                ]),
            AssociationField::new('team')
                ->setFormTypeOptions([
                    'attr' => [
                        'data-widget' => 'select2'
                    ]
                ]),
            CollectionField::new('trainingExercises')
                ->setLabel('Exercises')
                ->setEntryType(TrainingExerciseType::class)
                ->renderExpanded()
                ->allowAdd()
                ->allowDelete()
                ->setFormTypeOption('by_reference', false)
                ->hideOnIndex()
                ->setFormTypeOptions([
                    'error_bubbling' => false
                ]),
        ];
    }
}