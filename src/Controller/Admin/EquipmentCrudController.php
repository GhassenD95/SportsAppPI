<?php

namespace App\Controller\Admin;

use App\Entity\Equipment;
use App\Enum\EquipmentState;
use App\Enum\EquipmentType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EquipmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Equipment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Equipment')
            ->setPaginatorPageSize(20)
            ->setDefaultSort(['name' => 'ASC']);
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

            ChoiceField::new('type')
                ->setChoices(EquipmentType::choices())
                ->renderAsNativeWidget()
                ->setColumns('col-md-3'),

            ChoiceField::new('state')
                ->setChoices(EquipmentState::choices())
                ->renderAsNativeWidget()
                ->setColumns('col-md-3'),

            IntegerField::new('quantity')
                ->setColumns('col-md-3'),

            MoneyField::new('price')
                ->setCurrency('USD')
                ->setStoredAsCents(false)
                ->setColumns('col-md-3'),

            ImageField::new('image_url')
                ->setLabel('Image')
                ->setBasePath('uploads/equipment')
                ->setUploadDir('public/uploads/equipment')
                ->setRequired(false)
                ->setColumns('col-md-6')
                ,

            AssociationField::new('facility')
                ->setColumns('col-md-6')
                ->autocomplete(),

            AssociationField::new('team')
                ->setColumns('col-md-6')
                ->autocomplete(),
        ];
    }
}