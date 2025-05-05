<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-demo-users',
    description: 'Creates demo users for testing',
)]
class CreateDemoUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = [
            [
                'email' => 'admin@sportsapp.com',
                'password' => 'admin123',
                'name' => 'Admin',
                'lastname' => 'User',
                'roles' => ['ROLE_ADMIN'],
            ],
            [
                'email' => 'coach@sportsapp.com',
                'password' => 'coach123',
                'name' => 'Coach',
                'lastname' => 'User',
                'roles' => ['ROLE_COACH'],
            ],
            [
                'email' => 'manager@sportsapp.com',
                'password' => 'manager123',
                'name' => 'Manager',
                'lastname' => 'User',
                'roles' => ['ROLE_MANAGER'],
            ],
            [
                'email' => 'athlete@sportsapp.com',
                'password' => 'athlete123',
                'name' => 'Athlete',
                'lastname' => 'User',
                'roles' => ['ROLE_ATHLETE'],
            ],
        ];

        foreach ($users as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setName($userData['name']);
            $user->setLastname($userData['lastname']);
            $user->setRoles($userData['roles']);
            
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $userData['password']
            );
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
            $output->writeln(sprintf('Created demo user: %s', $userData['email']));
        }

        $this->entityManager->flush();

        $output->writeln('Demo users created successfully!');
        return Command::SUCCESS;
    }
} 