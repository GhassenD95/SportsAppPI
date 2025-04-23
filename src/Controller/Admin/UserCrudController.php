<?php

// src/Controller/Admin/UserCrudController.php
namespace App\Controller\Admin;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Random\RandomException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private MailerInterface $mailer,
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('Users')
            ->setHelp('new', 'Leave password blank to auto-generate')
            ->setHelp('edit', 'Leave password blank to keep current password');
    }

    public function configureFields(string $pageName): iterable
    {
        $passwordHelp = $pageName === Crud::PAGE_NEW
            ? 'Leave blank to auto-generate'
            : 'Leave blank to keep current password';

        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('name'),
            TextField::new('lastname'),
            TextField::new('email'),
            TextField::new('plainPassword')
                ->setLabel('Password')
                ->setFormType(PasswordType::class)
                ->setRequired(false)
                ->onlyOnForms()
                ->setHelp($passwordHelp),
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

    /**
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    public function persistEntity($entityManager, $user): void
    {
        // For new users only
        if (empty($user->getPlainPassword())) {
            $randomPassword = bin2hex(random_bytes(8));
            $user->setPlainPassword($randomPassword);
        }

        $this->hashPassword($user);
        parent::persistEntity($entityManager, $user);

        //send an email with update password form
        $token = new PasswordResetToken($user);
        $entityManager->persist($token);
        $entityManager->flush();
        $email = (new TemplatedEmail())
            ->from('noreply@yourdomain.com')
            ->to($user->getEmail())
            ->subject('Set Your Password')
            ->htmlTemplate('email/password_reset.html.twig')
            ->context([
                'user' => $user,
                'token' => $token->getToken(),
                'expires_at' => $token->getExpiresAt(),
            ]);

        $this->mailer->send($email);



    }

    public function updateEntity($entityManager, $user): void
    {
        // Only hash password if it was changed (plainPassword not empty)
        if ($user->getPlainPassword()) {
            $this->hashPassword($user);
        }

        parent::updateEntity($entityManager, $user);
    }

    private function hashPassword(User $user): void
    {
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                $user->getPlainPassword()
            )
        );
        $user->eraseCredentials();
    }
}