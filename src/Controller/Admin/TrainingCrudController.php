<?php
// src/Controller/Admin/TrainingCrudController.php
// src/Controller/Admin/TrainingCrudController.php
namespace App\Controller\Admin;

use App\Entity\Training;
use App\Entity\User;
use App\Form\TrainingExerciseType;
use Doctrine\ORM\EntityManagerInterface;
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
    public static function getEntityFqcn(): string
    {
        return Training::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Training Session')
            ->setEntityLabelInPlural('Training Sessions');
    }

    public function configureActions(Actions $actions): Actions
    {
        // This adds the "show" action to the index page
        return $actions
            ->add('index', 'detail');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('title'),
            TextEditorField::new('description')->onlyOnForms(),
            DateTimeField::new('startTime'),
            DateTimeField::new('endTime'),
            AssociationField::new('facility'),

            AssociationField::new('coach')
                ->setQueryBuilder(
                    fn (QueryBuilder $queryBuilder) => $queryBuilder
                        ->andWhere('entity.roles LIKE :role')
                        ->setParameter('role', '%ROLE_COACH%')
                ),

            AssociationField::new('team'),

            CollectionField::new('trainingExercises')
                ->setLabel('Exercises')
                ->setEntryType(TrainingExerciseType::class)
                ->renderExpanded()
                ->allowAdd()
                ->allowDelete()
                ->setFormTypeOption('by_reference', false)
                ->hideOnIndex(),
        ];
    }

}