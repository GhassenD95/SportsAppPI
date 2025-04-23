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
        $i = 0;
        while ($this->hasReference('athlete_' . $i, User::class)) {
            $athleteReferences[] = 'athlete_' . $i;
            $i++;
        }

        // Create teams (2 per sport)
        $teams = [];
        for ($i = 0; $i < 6; $i++) {
            $sport = $sports[$i % 3];
            $team = new Team();
            $team->setName($this->generateTeamName($faker, $sport));
            $team->setSport($sport);
            $team->setLogoUrl($this->getTeamLogo($sport));

            // Assign random coach (from coach_0 to coach_4)
            $coachRef = 'coach_' . rand(0, 4);
            $team->setCoach($this->getReference($coachRef, User::class));

            $manager->persist($team);
            $teams[] = $team;
            $this->addReference('team_' . $i, $team, Team::class);
        }

        // Randomly assign athletes to teams
        if (!empty($athleteReferences)) {
            foreach ($athleteReferences as $athleteRef) {
                /** @var User $athlete */
                $athlete = $this->getReference($athleteRef, User::class);

                // Assign to random team
                $randomTeam = $teams[array_rand($teams)];
                $randomTeam->addPlayer($athlete);
                $athlete->setTeam($randomTeam);

                $manager->persist($athlete);
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
    private function getTeamLogo(string $teamName): string
    {
        $name = urlencode($teamName);
        return "https://ui-avatars.com/api/?name=$name&background=random&color=fff&size=400";
    }


    public function getDependencies(): array
    {
        return [
            UserFixtures::class
        ];
    }
}