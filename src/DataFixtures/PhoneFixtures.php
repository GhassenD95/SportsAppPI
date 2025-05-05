<?php

namespace App\DataFixtures;

use App\Entity\Phone;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PhoneFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Get all users from UserFixtures
        $users = $manager->getRepository(User::class)->findAll();

        // Phone types
        $phoneTypes = ['mobile', 'home', 'work', 'emergency'];

        // Generate phone numbers for each user
        foreach ($users as $user) {
            // Add 1-2 phone numbers per user
            $numPhones = rand(1, 2);
            
            for ($i = 0; $i < $numPhones; $i++) {
                $phone = new Phone();
                $phone->setUser($user);
                
                // Generate a random phone number
                $phoneNumber = $this->generatePhoneNumber();
                $phone->setNumber($phoneNumber);
                
                // Set a random phone type
                $phone->setType($phoneTypes[array_rand($phoneTypes)]);
                
                // Randomly verify some phone numbers
                $phone->setVerified(rand(0, 1) == 1);
                
                // Verification is handled by the isVerified method

                $manager->persist($phone);
            }
        }

        $manager->flush();
    }

    private function generatePhoneNumber(): string
    {
        // Generate a random phone number in a format that fits the column length
        return sprintf(
            '+%d%d%d%d%d%d%d%d%d%d', 
            rand(1, 9),     // First digit of country code
            rand(0, 9),     // Second digit of country code
            rand(0, 9),     // Area code first digit
            rand(0, 9),     // Area code second digit
            rand(0, 9),     // Area code third digit
            rand(0, 9),     // First part first digit
            rand(0, 9),     // First part second digit
            rand(0, 9),     // First part third digit
            rand(0, 9),     // Second part first digit
            rand(0, 9)      // Second part second digit
        );
    }

    // Verification code method removed

    public function getDependencies(): array
    {
        return [
            UserFixtures::class
        ];
    }
}
