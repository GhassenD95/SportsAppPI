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

        // Get all athlete references that exist
        $athleteReferences = [];
        for ($i = 0; $i < 20; $i++) {
            try {
                $athleteReferences[] = 'athlete-' . $i;
            } catch (\Exception $e) {
                break;
            }
        }

        // Create teams (2 per sport)
        $teams = [];
        for ($i = 0; $i < 6; $i++) {
            $sport = $sports[$i % 3];
            $team = new Team();
            $team->setName($this->generateTeamName($faker, $sport));
            $team->setSport($sport);
            $team->setLogoUrl($this->getTeamLogo($sport));

            // Assign random coach (from coach-0 to coach-4)
            try {
                $coachRef = 'coach-' . rand(0, 4);
                $team->setCoach($this->getReference($coachRef, User::class));
            } catch (\Exception $e) {
                // If coach reference doesn't exist, use the first available coach
                $team->setCoach($this->getReference('coach-0', User::class));
            }

            $manager->persist($team);
            $teams[] = $team;
            $this->addReference('team-' . $i, $team);
        }

        // Randomly assign athletes to teams
        if (!empty($athleteReferences)) {
            foreach ($athleteReferences as $athleteRef) {
                try {
                    /** @var User $athlete */
                    $athlete = $this->getReference($athleteRef, User::class);

                    // Assign to random team
                    $randomTeam = $teams[array_rand($teams)];
                    $randomTeam->addPlayer($athlete);
                    $athlete->setTeam($randomTeam);

                    $manager->persist($athlete);
                } catch (\Exception $e) {
                    continue;
                }
            }
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
        return "https://ui-avatars.com/api/?name=$sport&background=random&color=fff&size=400";
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class
        ];
    }
}