<?php

namespace App\Controller\Admin;

use App\Entity\MatchEvent;
use App\Entity\Team;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FileUploadType;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class MatchEventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MatchEvent::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Match')
            ->setEntityLabelInPlural('Matches')
            ->setDefaultSort(['date' => 'DESC'])
            ->setSearchFields(['homeTeam.name', 'location', 'date'])
            ->setFormOptions([
                'validation_groups' => ['Default', 'creation']
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnDetail();

        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            // Main match details panel
            yield FormField::addPanel('Match Details')->setIcon('fa fa-calendar');
            yield FormField::addRow();
            yield DateTimeField::new('date')
                ->setFormat('yyyy-MM-dd HH:mm')
                ->setColumns('col-md-6')
                ->setRequired(true)
                ->setFormTypeOptions([
                    'constraints' => [
                        new NotBlank(['message' => 'Please select a date and time for the match']),
                    ],
                ]);

            yield TextField::new('location')
                ->setColumns('col-md-6')
                ->setRequired(true)
                ->setFormTypeOptions([
                    'constraints' => [
                        new NotBlank(['message' => 'Please enter a location for the match']),
                    ],
                ]);

            yield FormField::addRow();
            yield AssociationField::new('homeTeam')
                ->setColumns('col-md-6')
                ->setFormTypeOptions([
                    'class' => Team::class,
                    'constraints' => [
                        new NotBlank(['message' => 'Please select a home team']),
                    ],
                ])
                ->setRequired(true)
                ->autocomplete();

            yield AssociationField::new('tournament')
                ->setColumns('col-md-6')
                ->autocomplete();

            // Away team panel
            yield FormField::addPanel('Away Team Information')->setIcon('fa fa-users');
            yield FormField::addRow();
            yield TextField::new('awayTeamName')
                ->setLabel('Team Name')
                ->setColumns('col-md-4')
                ->onlyOnForms()
                ->setFormTypeOption('mapped', false)
                ->setRequired(true)
                ->setFormTypeOptions([
                    'constraints' => [
                        new NotBlank(['message' => 'Please enter the away team name']),
                    ],
                ]);

            yield TextField::new('awayTeamClub')
                ->setLabel('Club')
                ->setColumns('col-md-4')
                ->onlyOnForms()
                ->setFormTypeOption('mapped', false)
                ->setRequired(true)
                ->setFormTypeOptions([
                    'constraints' => [
                        new NotBlank(['message' => 'Please enter the away team club']),
                    ],
                ]);

            yield ImageField::new('awayTeamLogo')
                ->setLabel('Logo')
                ->setColumns('col-md-4')
                ->setBasePath('/uploads/teams/away')
                ->setUploadDir('public/uploads/teams/away')
                ->setFormType(FileUploadType::class)
                ->onlyOnForms()
                ->setFormTypeOption('mapped', false);

            // Score panel
            yield FormField::addPanel('Match Score')->setIcon('fa fa-futbol');
            yield FormField::addRow();
            yield IntegerField::new('homeScore')
                ->setLabel('Home Score')
                ->setColumns('col-md-3')
                ->onlyOnForms()
                ->setFormTypeOption('mapped', false)
                ->setRequired(true)
                ->setFormTypeOptions([
                    'constraints' => [
                        new Range([
                            'min' => 0,
                            'minMessage' => 'Score cannot be negative',
                        ]),
                    ],
                ]);

            yield IntegerField::new('awayScore')
                ->setLabel('Away Score')
                ->setColumns('col-md-3')
                ->onlyOnForms()
                ->setFormTypeOption('mapped', false)
                ->setRequired(true)
                ->setFormTypeOptions([
                    'constraints' => [
                        new Range([
                            'min' => 0,
                            'minMessage' => 'Score cannot be negative',
                        ]),
                    ],
                ]);
        } else {
            // Index and detail pages
            yield DateTimeField::new('date')->setFormat('yyyy-MM-dd HH:mm');
            yield TextField::new('location');
            yield AssociationField::new('homeTeam');
            yield AssociationField::new('tournament');
            yield TextField::new('awayTeamDisplay', 'Away Team');
            yield TextField::new('scoreDisplay', 'Score');
        }
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add('index', 'detail');
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $builder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        return $this->configureFormBuilder($builder);
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $builder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        return $this->configureFormBuilder($builder);
    }

    private function configureFormBuilder(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $match = $event->getData();
            $form = $event->getForm();

            if ($match instanceof MatchEvent) {
                $awayTeam = $match->getAwayTeam();
                $score = $match->getScore();

                $form->get('awayTeamName')->setData($awayTeam['name'] ?? '');
                $form->get('awayTeamClub')->setData($awayTeam['club'] ?? '');
                $form->get('awayTeamLogo')->setData($awayTeam['logo'] ?? null);
                $form->get('homeScore')->setData($score['home'] ?? 0);
                $form->get('awayScore')->setData($score['away'] ?? 0);
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $match = $event->getData();
            $form = $event->getForm();

            $match->setAwayTeam([
                'name' => $form->get('awayTeamName')->getData(),
                'club' => $form->get('awayTeamClub')->getData(),
                'logo' => $form->get('awayTeamLogo')->getData(),
            ]);

            $match->setScore([
                'home' => (int) $form->get('homeScore')->getData(),
                'away' => (int) $form->get('awayScore')->getData(),
            ]);
        });

        return $builder;
    }

    public function persistEntity($entityManager, $entityInstance): void
    {
        $this->initializeDefaultValues($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity($entityManager, $entityInstance): void
    {
        $this->initializeDefaultValues($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function initializeDefaultValues(MatchEvent $match): void
    {
        if ($match->getAwayTeam() === null) {
            $match->setAwayTeam(['name' => '', 'club' => '', 'logo' => null]);
        }

        if ($match->getScore() === null) {
            $match->setScore(['home' => 0, 'away' => 0]);
        }
    }
}