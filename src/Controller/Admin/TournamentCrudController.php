<?php

namespace App\Controller\Admin;

use App\Entity\Tournament;
use App\Entity\Team;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;

class TournamentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tournament::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Tournament')
            ->setEntityLabelInPlural('Tournaments')
            ->setSearchFields(['name'])
            ->setDefaultSort(['start_date' => 'DESC']);
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

            TextField::new('name')
                ->setColumns('col-md-6'),

            DateField::new('start_date')
                ->setFormat('yyyy-MM-dd')
                ->setColumns('col-md-3'),

            DateField::new('end_date')
                ->setFormat('yyyy-MM-dd')
                ->setColumns('col-md-3'),

            AssociationField::new('teams')
                ->setFormTypeOption('by_reference', false)
                ->autocomplete()
                ->formatValue(function ($value, $entity) {
                    $teamNames = [];
                    foreach ($entity->getTeams() as $team) {
                        $teamNames[] = $team->getName();
                    }
                    return implode(', ', $teamNames);
                })
                ->setColumns('col-md-12')
        ];
    }
}