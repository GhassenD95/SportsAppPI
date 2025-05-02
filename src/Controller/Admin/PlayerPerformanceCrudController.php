<?php

namespace App\Controller\Admin;

use App\Entity\PlayerPerformance;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
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

class PlayerPerformanceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PlayerPerformance::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Player Performance')
            ->setEntityLabelInPlural('Player Performances')
            ->setSearchFields(['player.name', 'player.lastname', 'team.name'])
            ->setDefaultSort(['performanceDate' => 'DESC'])
            ->setPageTitle('index', 'Player Performances')
            ->setPageTitle('new', 'Create Player Performance')
            ->setPageTitle('edit', 'Edit Player Performance');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('player'))
            ->add(EntityFilter::new('team'))
            ->add(DateTimeFilter::new('performanceDate'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('player', 'Player')
                ->setRequired(true)
                ->formatValue(fn($value, PlayerPerformance $performance) => $performance->getPlayer()?->getFullName())
                ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                    return $queryBuilder
                        ->andWhere('entity.roles LIKE :role')
                        ->setParameter('role', '%ROLE_ATHLETE%');
                }),
            AssociationField::new('team', 'Team')
                ->setRequired(true),
            DateField::new('performanceDate', 'Performance Date')
                ->setRequired(true),
            IntegerField::new('goalsScored', 'Goals Scored')
                ->setRequired(true),
            IntegerField::new('assists', 'Assists')
                ->setRequired(true),
            IntegerField::new('minutesPlayed', 'Minutes Played')
                ->setRequired(true),
            IntegerField::new('shotsOnTarget', 'Shots on Target')
                ->setRequired(true),
            IntegerField::new('passesCompleted', 'Passes Completed')
                ->setRequired(true),
            IntegerField::new('tackles', 'Tackles')
                ->setRequired(true),
            IntegerField::new('interceptions', 'Interceptions')
                ->setRequired(true),
            IntegerField::new('saves', 'Saves')
                ->setRequired(true),
            NumberField::new('rating', 'Rating')
                ->setRequired(true)
                ->setNumDecimals(1),
            TextareaField::new('notes', 'Notes'),
        ];
    }
} 