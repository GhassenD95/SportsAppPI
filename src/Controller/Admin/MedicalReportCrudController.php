<?php

namespace App\Controller\Admin;

use App\Entity\MedicalReport;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;

class MedicalReportCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MedicalReport::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Medical Report')
            ->setEntityLabelInPlural('Medical Reports')
            ->setSearchFields(['player.name', 'player.lastname', 'team.name', 'diagnosis', 'status'])
            ->setDefaultSort(['reportDate' => 'DESC'])
            ->setPageTitle('index', 'Medical Reports')
            ->setPageTitle('new', 'Create Medical Report')
            ->setPageTitle('edit', 'Edit Medical Report');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('player'))
            ->add(EntityFilter::new('team'))
            ->add(DateTimeFilter::new('reportDate'))
            ->add('status');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('player', 'Player')
                ->setRequired(true)
                ->formatValue(fn($value, MedicalReport $report) => $report->getPlayer()?->getFullName())
                ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                    return $queryBuilder
                        ->andWhere('entity.roles LIKE :role')
                        ->setParameter('role', '%ROLE_ATHLETE%');
                }),
            AssociationField::new('team', 'Team')
                ->setRequired(true),
            DateField::new('reportDate', 'Report Date')
                ->setRequired(true),
            TextField::new('diagnosis', 'Diagnosis')
                ->setRequired(true),
            TextareaField::new('treatment', 'Treatment')
                ->setRequired(true),
            TextareaField::new('notes', 'Notes'),
            DateField::new('followUpDate', 'Follow-up Date'),
            TextField::new('status', 'Status')
                ->setRequired(true),
        ];
    }
} 