<?php

// src/Controller/Admin/TeamCrudController.php
namespace App\Controller\Admin;

use App\Entity\Team;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TeamCrudController extends AbstractCrudController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public static function getEntityFqcn(): string
    {
        return Team::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Team')
            ->setEntityLabelInPlural('Teams')
            ->setSearchFields(['name', 'coach', 'players'])
            ->setDefaultSort(['name' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();

        yield TextField::new('name')
            ->setColumns(6);

        // Coach field
        yield AssociationField::new('coach')
            ->setCrudController(UserCrudController::class)
            ->autocomplete()
            ->formatValue(function ($value, Team $team) {
                return $team->getCoach() ? $team->getCoach()->getFullName() : '';
            })
            ->setQueryBuilder(
                fn ($queryBuilder) => $queryBuilder
                    ->andWhere('entity.roles LIKE :role')
                    ->setParameter('role', '%ROLE_COACH%')
            )
            ->setColumns(6);

        // Players field - fixed duplicate QueryBuilder
        yield AssociationField::new('players')
            ->setCrudController(UserCrudController::class)
            ->autocomplete()
            ->setFormTypeOption('by_reference', false) // Crucial for proper persistence
            ->formatValue(function ($value, Team $team) {
                return implode(', ', $team->getPlayers()->map(
                    fn ($player) => $player->getFullName()
                )->toArray());
            })
            ->setQueryBuilder(
                fn ($queryBuilder) => $queryBuilder
                    ->andWhere('entity.roles LIKE :role')
                    ->setParameter('role', '%ROLE_ATHLETE%')
            )
            ->setColumns(6);

        yield ImageField::new('logoUrl')
            ->setBasePath('uploads/teams')
            ->setUploadDir('public/uploads/teams')
            ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
            ->setRequired(false)
            ->setColumns(6);
    }

    // Add these methods to ensure proper persistence
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->updatePlayersTeam($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->updatePlayersTeam($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function updatePlayersTeam(Team $team): void
    {
        foreach ($team->getPlayers() as $player) {
            $player->setTeam($team);
            $this->entityManager->persist($player);
        }
    }
}