<?php

namespace App\Controller\Admin;

use App\Entity\Facility;
use App\Enum\Sport;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class FacilityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Facility::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('name'),
            TextField::new('location'),
            AssociationField::new('manager')
                ->formatValue(function ($value, $entity) {
                    return $entity->getManager()?->getEmail();
                })
                ->setQueryBuilder(
                    fn(QueryBuilder $qb) => $qb
                        ->andWhere('entity.roles LIKE :role')
                        ->setParameter('role', '%ROLE_MANAGER%')
                )
                ->autocomplete(),
            ChoiceField::new('sports')
                ->setChoices(Sport::choices())
                ->allowMultipleChoices()
                ->renderExpanded()
                ->setHelp('Select available sports'),
            ImageField::new('image_url')
                ->setBasePath('uploads/facilities')
                ->setUploadDir('public/uploads/facilities')
                ->setLabel('Facility Image')
                ->setRequired(false),
        ];
    }
}