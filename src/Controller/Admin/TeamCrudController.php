<?php

// src/Controller/Admin/TeamCrudController.php
namespace App\Controller\Admin;

use App\Entity\Team;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TeamCrudController extends AbstractCrudController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Team::class;
    }
    public function configureActions(Actions $actions): Actions
    {
        // This adds the "show" action to the index page
        return $actions
            ->add('index', 'detail');
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Team')
            ->setEntityLabelInPlural('Teams')
            ->setSearchFields(['name','coach.name','coach.lastname', 'players.name', 'sport'])
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();

        yield TextField::new('name')
            ->setColumns(6);

        yield ChoiceField::new('sport')
            ->setChoices([
                'Football' => 'football',
                'Basketball' => 'basketball',
                'Volleyball' => 'volleyball'
            ]);
        // Coach field
        yield AssociationField::new('coach')
            ->setCrudController(UserCrudController::class)
            ->autocomplete()
            ->formatValue(fn($value, Team $team) => $team->getCoach()?->getFullName())
            ->setQueryBuilder(fn($qb) => $qb->andWhere('entity.roles LIKE :role')->setParameter('role', '%ROLE_COACH%'))
            ->setColumns(6);

        // Players field
// src/Controller/Admin/TeamCrudController.php
        yield AssociationField::new('players')
            ->setCrudController(UserCrudController::class)
            ->autocomplete()
            ->setFormTypeOption('by_reference', false)
            ->setQueryBuilder(fn($qb) => $qb->andWhere('entity.roles LIKE :role')->setParameter('role', '%ROLE_ATHLETE%'))
            ->setColumns(6)
            ->formatValue(function ($value, Team $team) use ($pageName) {
                $playerNames = $team->getPlayers()->map(fn($p) => $p->getFullName())->toArray();

                if ($pageName === Crud::PAGE_INDEX) {
                    return count($playerNames) > 3
                        ? implode(', ', array_slice($playerNames, 0, 3)) . '... (+'.(count($playerNames)-3).' more)'
                        : implode(', ', $playerNames);
                }

                return implode(', ', array_map(function ($player) use ($team) {
                    $url = $this->generateUrl('admin_user_detail', ['entityId' => $player->getId()]);
                    return sprintf('<a href="%s">%s</a>', $url, $player);
                }, $team->getPlayers()->toArray()));
            });
        yield ImageField::new('logoUrl')
            ->setLabel('Logo')
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