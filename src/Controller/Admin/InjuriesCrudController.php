<?php

namespace App\Controller\Admin;

use App\Entity\Injuries;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class InjuriesCrudController extends AbstractCrudController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Injuries::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add('index', 'detail');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Injury')
            ->setEntityLabelInPlural('Injuries')
            ->setSearchFields(['player.name', 'player.lastname', 'team.name', 'injuryType', 'status'])
            ->setDefaultSort(['injuryDate' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();

        yield AssociationField::new('player')
            ->setCrudController(UserCrudController::class)
            ->autocomplete()
            ->formatValue(fn($value, Injuries $injury) => $injury->getPlayer()?->getFullName())
            ->setQueryBuilder(fn($qb) => $qb->andWhere('entity.roles LIKE :role')->setParameter('role', '%ROLE_ATHLETE%'))
            ->setColumns(6);

        yield AssociationField::new('team')
            ->setCrudController(TeamCrudController::class)
            ->autocomplete()
            ->setColumns(6);

        yield ChoiceField::new('injuryType')
            ->setChoices([
                'Sprain' => 'SPRAIN',
                'Strain' => 'STRAIN',
                'Fracture' => 'FRACTURE',
                'Concussion' => 'CONCUSSION',
                'Contusion' => 'CONTUSION',
                'Dislocation' => 'DISLOCATION',
                'Tendonitis' => 'TENDONITIS',
                'Other' => 'OTHER',
            ])
            ->setColumns(6);

        yield ChoiceField::new('severity')
            ->setChoices([
                'Minor' => 'MINOR',
                'Moderate' => 'MODERATE',
                'Severe' => 'SEVERE',
            ])
            ->setColumns(6);

        yield DateTimeField::new('injuryDate')
            ->setFormat('yyyy-MM-dd HH:mm')
            ->setColumns(4);

        yield DateTimeField::new('expectedRecoveryDate')
            ->setFormat('yyyy-MM-dd HH:mm')
            ->setColumns(4);

        yield DateTimeField::new('actualRecoveryDate')
            ->setFormat('yyyy-MM-dd HH:mm')
            ->setColumns(4);

        yield TextareaField::new('description')
            ->setColumns(12)
            ->hideOnIndex();

        yield TextareaField::new('treatmentPlan')
            ->setColumns(12)
            ->hideOnIndex();

        yield ChoiceField::new('status')
            ->setChoices([
                'Active' => 'ACTIVE',
                'Recovered' => 'RECOVERED',
                'Chronic' => 'CHRONIC',
            ])
            ->setColumns(6);

        yield AssociationField::new('medicalReport')
            ->setCrudController(MedicalReportCrudController::class)
            ->autocomplete()
            ->setColumns(6);

        yield TextareaField::new('notes')
            ->setColumns(12)
            ->hideOnIndex();
    }
} 