<?php

namespace App\DataFixtures;

use App\Entity\Team;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TeamFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $sports = ['football', 'basketball', 'volleyball'];
        $faker = Factory::create();

        // Create 6 teams (2 per sport)
        for ($i = 0; $i < 6; $i++) {
            $sport = $sports[$i % 3];
            $team = new Team();
            $team->setName($this->generateTeamName($faker, $sport));
            $team->setSport($sport);
            $team->setLogoUrl($this->getTeamLogo($sport));

            // Correct reference usage per Symfony docs
            $coachRef = 'coach_' . rand(0, 4);
            $team->setCoach($this->getReference($coachRef, User::class));

            $manager->persist($team);
            $this->addReference('team_' . $i, $team, Team::class);
        }

        $manager->flush();
    }

    private function generateTeamName($faker, string $sport): string
    {
        $formats = [
            'football' => ['{city} FC', '{city} United', '{city} Athletic'],
            'basketball' => ['{city} Bulls', '{city} Lakers', '{city} Warriors'],
            'volleyball' => ['{city} Volley', '{city} Spikers', '{city} Smash']
        ];

        $template = $formats[$sport][array_rand($formats[$sport])];
        return str_replace('{city}', $faker->city(), $template);
    }

    private function getTeamLogo(string $sport): string
    {
        $query = urlencode($sport . ' team logo');
        return "https://source.unsplash.com/random/400x400/?{$query}";
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class
        ];
    }
}