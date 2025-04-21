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
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Create Admin
        $admin = new User();
        $admin->setEmail('admin@admin.com');
        $admin->setName('Admin');
        $admin->setLastname('User');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setImageUrl($this->getProfileImage('admin'));
        $manager->persist($admin);

        // Create 3 Managers
        for ($i = 0; $i < 3; $i++) {
            $managerUser = new User();
            $managerUser->setEmail($faker->unique()->safeEmail());
            $managerUser->setName($faker->firstName());
            $managerUser->setLastname($faker->lastName());
            $managerUser->setRoles(['ROLE_MANAGER']);
            $managerUser->setPassword($this->passwordHasher->hashPassword($managerUser, 'manager123'));
            $managerUser->setImageUrl($this->getProfileImage('manager'));
            $manager->persist($managerUser);
            $this->addReference('manager_'.$i, $managerUser);
        }

        // Create 5 Coaches
        for ($i = 0; $i < 5; $i++) {
            $coach = new User();
            $coach->setEmail($faker->unique()->safeEmail());
            $coach->setName($faker->firstName());
            $coach->setLastname($faker->lastName());
            $coach->setRoles(['ROLE_COACH']);
            $coach->setPassword($this->passwordHasher->hashPassword($coach, 'coach123'));
            $coach->setImageUrl($this->getProfileImage('coach'));
            $manager->persist($coach);
            $this->addReference('coach_'.$i, $coach);
        }

        // Create 20 Athletes
        for ($i = 0; $i < 20; $i++) {
            $athlete = new User();
            $athlete->setEmail($faker->unique()->safeEmail());
            $athlete->setName($faker->firstName());
            $athlete->setLastname($faker->lastName());
            $athlete->setRoles(['ROLE_ATHLETE']);
            $athlete->setPassword($this->passwordHasher->hashPassword($athlete, 'athlete123'));
            $athlete->setImageUrl($this->getProfileImage('athlete'));
            $manager->persist($athlete);
            $this->addReference('athlete_'.$i, $athlete);
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