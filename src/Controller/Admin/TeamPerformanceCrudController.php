<?php

namespace App\Controller\Admin;

use App\Entity\TeamPerformance;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;

class TeamPerformanceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TeamPerformance::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Team Performance')
            ->setEntityLabelInPlural('Team Performances')
            ->setSearchFields(['team.name'])
            ->setDefaultSort(['performanceDate' => 'DESC'])
            ->setPageTitle('index', 'Team Performances')
            ->setPageTitle('new', 'Create Team Performance')
            ->setPageTitle('edit', 'Edit Team Performance');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('team'))
            ->add(DateTimeFilter::new('performanceDate'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('team', 'Team')
                ->setRequired(true),
            DateField::new('performanceDate', 'Performance Date')
                ->setRequired(true),
            IntegerField::new('goalsScored', 'Goals Scored')
                ->setRequired(true),
            IntegerField::new('goalsConceded', 'Goals Conceded')
                ->setRequired(true),
            IntegerField::new('shotsOnTarget', 'Shots on Target')
                ->setRequired(true),
            IntegerField::new('shotsConceded', 'Shots Conceded')
                ->setRequired(true),
            IntegerField::new('possessionPercentage', 'Possession %')
                ->setRequired(true),
            IntegerField::new('passesCompleted', 'Passes Completed')
                ->setRequired(true),
            IntegerField::new('tackles', 'Tackles')
                ->setRequired(true),
            IntegerField::new('interceptions', 'Interceptions')
                ->setRequired(true),
            IntegerField::new('fouls', 'Fouls')
                ->setRequired(true),
            IntegerField::new('yellowCards', 'Yellow Cards')
                ->setRequired(true),
            IntegerField::new('redCards', 'Red Cards')
                ->setRequired(true),
            NumberField::new('rating', 'Rating')
                ->setRequired(true)
                ->setNumDecimals(1),
            TextareaField::new('notes', 'Notes'),
        ];
    }
} 