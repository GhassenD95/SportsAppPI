<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('Users');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('name'),
            TextField::new('lastname'),
            TextField::new('email'),
            TextField::new('plainPassword')
                ->setLabel('Password')
                ->setFormType(PasswordType::class)
                ->setRequired($pageName === Crud::PAGE_NEW)
                ->onlyOnForms()
                ->setHelp('Minimum 6 characters'),
            ImageField::new('imageUrl')
                ->setUploadDir('public/uploads/users')
                ->setBasePath('uploads/users')
                ->setRequired(false),
            DateTimeField::new('created_at')->setFormat('dd/MM/yyyy HH:mm:ss')->onlyOnIndex(),
            DateTimeField::new('updated_at')->setFormat('dd/MM/yyyy HH:mm:ss')->onlyOnIndex(),
            ChoiceField::new('roles')
                ->setChoices([
                    'Athlete' => 'ROLE_ATHLETE',
                    'Admin' => 'ROLE_ADMIN',
                    'Manager' => 'ROLE_MANAGER',
                    'Coach' => 'ROLE_COACH',
                ])
                ->allowMultipleChoices(),
        ];
    }

    public function persistEntity($entityManager, $user): void
    {
        $this->hashPassword($user);
        parent::persistEntity($entityManager, $user);
    }

    public function updateEntity($entityManager, $user): void
    {
        $this->hashPassword($user);
        parent::updateEntity($entityManager, $user);
    }

    private function hashPassword(User $user): void
    {
        if ($user->getPlainPassword()) {
            $user->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    $user->getPlainPassword()
                )
            );
            $user->eraseCredentials();
        }
    }
}