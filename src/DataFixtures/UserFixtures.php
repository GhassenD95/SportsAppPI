<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const ADMIN_REFERENCE = 'admin-user';
    private const ROLES = [
        'manager' => ['ROLE_MANAGER', 3],
        'coach' => ['ROLE_COACH', 5],
        'athlete' => ['ROLE_ATHLETE', 20]
    ];

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Create static demo users
        $demoUsers = [
            [
                'email' => 'demo_admin@sports.com',
                'reference' => 'demo-admin',
                'role' => 'ROLE_ADMIN',
                'name' => 'Admin',
                'lastname' => 'Demo',
                'password' => 'demo_admin123',
                'imageType' => 'admin'
            ],
            [
                'email' => 'demo_manager@sports.com',
                'reference' => 'demo-manager',
                'role' => 'ROLE_MANAGER',
                'name' => 'Manager',
                'lastname' => 'Demo',
                'password' => 'demo_manager123',
                'imageType' => 'manager'
            ],
            [
                'email' => 'demo_coach@sports.com',
                'reference' => 'demo-coach',
                'role' => 'ROLE_COACH',
                'name' => 'Coach',
                'lastname' => 'Demo',
                'password' => 'demo_coach123',
                'imageType' => 'coach'
            ],
            [
                'email' => 'demo_athlete@sports.com',
                'reference' => 'demo-athlete',
                'role' => 'ROLE_ATHLETE',
                'name' => 'Athlete',
                'lastname' => 'Demo',
                'password' => 'demo_athlete123',
                'imageType' => 'athlete'
            ]
        ];

        // Create demo users
        foreach ($demoUsers as $userData) {
            $this->createUser(
                manager: $manager,
                email: $userData['email'],
                reference: $userData['reference'],
                role: $userData['role'],
                name: $userData['name'],
                lastname: $userData['lastname'],
                password: $userData['password'],
                imageType: $userData['imageType']
            );
        }

        // Create admin (only if doesn't exist)
        $this->createUser(
            manager: $manager,
            email: 'admin2@admin.com',
            reference: self::ADMIN_REFERENCE,
            role: 'ROLE_ADMIN',
            name: 'Admin2',
            lastname: 'User',
            password: 'admin123',
            imageType: 'admin'
        );

        // Create dynamic users for each role
        foreach (self::ROLES as $rolePrefix => [$role, $count]) {
            for ($i = 0; $i < $count; $i++) {
                $this->createUser(
                    manager: $manager,
                    email: "{$rolePrefix}" . ($i + 1) . '@example.com',
                    reference: "{$rolePrefix}-{$i}",
                    role: $role,
                    name: $faker->firstName(),
                    lastname: $faker->lastName(),
                    password: "{$rolePrefix}123",
                    imageType: $rolePrefix
                );
            }
        }

        $manager->flush();
    }

    private function createUser(
        ObjectManager $manager,
        string $email,
        string $reference,
        string $role,
        string $name,
        string $lastname,
        string $password,
        string $imageType
    ): void {
        $user = $manager->getRepository(User::class)->findOneBy(['email' => $email]) ?? new User();

        if (!$user->getId()) {
            $user->setEmail($email)
                ->setName($name)
                ->setLastname($lastname)
                ->setRoles([$role])
                ->setPassword($this->passwordHasher->hashPassword($user, $password))
                ->setImageUrl($this->getProfileImage($imageType));

            $manager->persist($user);
        }

        $this->setReference($reference, $user);
    }

    private function getProfileImage(string $type): string
    {
        $gender = (rand(0, 1) === 0) ? 'men' : 'women';
        $id = rand(1, 99);
        return "https://randomuser.me/api/portraits/{$gender}/{$id}.jpg";
    }
}