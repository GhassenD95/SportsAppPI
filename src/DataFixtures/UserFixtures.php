<?php

// src/DataFixtures/UserFixtures.php
namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const ADMIN_REFERENCE = 'admin-user';
    
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Create admin user if it doesn't exist
        $admin = $manager->getRepository(User::class)->findOneBy(['email' => 'admin2@admin.com']);
        if (!$admin) {
            $admin = new User();
            $admin->setEmail('admin2@admin.com');
            $admin->setName('Admin2');
            $admin->setLastname('User');
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
            $admin->setImageUrl($this->getProfileImage('admin'));
            $manager->persist($admin);
            $manager->flush(); // Flush immediately to get the ID
        }
        $this->setReference(self::ADMIN_REFERENCE, $admin);

        // Create managers if they don't exist
        for ($i = 0; $i < 3; $i++) {
            $email = 'manager' . ($i + 1) . '@example.com';
            $managerUser = $manager->getRepository(User::class)->findOneBy(['email' => $email]);
            if (!$managerUser) {
                $managerUser = new User();
                $managerUser->setEmail($email);
                $managerUser->setName($faker->firstName());
                $managerUser->setLastname($faker->lastName());
                $managerUser->setRoles(['ROLE_MANAGER']);
                $managerUser->setPassword($this->passwordHasher->hashPassword($managerUser, 'manager123'));
                $managerUser->setImageUrl($this->getProfileImage('manager'));
                $manager->persist($managerUser);
            }
            $this->setReference('manager-' . $i, $managerUser);
        }

        // Create coaches if they don't exist
        for ($i = 0; $i < 5; $i++) {
            $email = 'coach' . ($i + 1) . '@example.com';
            $coach = $manager->getRepository(User::class)->findOneBy(['email' => $email]);
            if (!$coach) {
                $coach = new User();
                $coach->setEmail($email);
                $coach->setName($faker->firstName());
                $coach->setLastname($faker->lastName());
                $coach->setRoles(['ROLE_COACH']);
                $coach->setPassword($this->passwordHasher->hashPassword($coach, 'coach123'));
                $coach->setImageUrl($this->getProfileImage('coach'));
                $manager->persist($coach);
            }
            $this->setReference('coach-' . $i, $coach);
        }

        // Create athletes if they don't exist
        for ($i = 0; $i < 20; $i++) {
            $email = 'athlete' . ($i + 1) . '@example.com';
            $athlete = $manager->getRepository(User::class)->findOneBy(['email' => $email]);
            if (!$athlete) {
                $athlete = new User();
                $athlete->setEmail($email);
                $athlete->setName($faker->firstName());
                $athlete->setLastname($faker->lastName());
                $athlete->setRoles(['ROLE_ATHLETE']);
                $athlete->setPassword($this->passwordHasher->hashPassword($athlete, 'athlete123'));
                $athlete->setImageUrl($this->getProfileImage('athlete'));
                $manager->persist($athlete);
            }
            $this->setReference('athlete-' . $i, $athlete);
        }

        $manager->flush();
    }

    private function getProfileImage(string $role): string
    {
        // Using randomuser.me API to get realistic headshots of people
        $gender = (rand(0, 1) === 0) ? 'men' : 'women';
        $id = rand(1, 99);

        return "https://randomuser.me/api/portraits/{$gender}/{$id}.jpg";
    }
}