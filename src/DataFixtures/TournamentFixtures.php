<?php

namespace App\DataFixtures;

use App\Entity\Team;
use App\Entity\Tournament;
use App\Entity\User;
use App\Enum\Sport;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TournamentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Get all team references
        $teamReferences = [];
        $i = 0;
        while ($this->hasReference('team_'.$i, Team::class)) {
            $teamReferences[] = 'team_'.$i;
            $i++;
        }

        // Create tournaments for each sport type
        foreach (Sport::cases() as $sport) {
            // Create 3-4 tournaments per sport
            $tournamentCount = rand(3, 4);

            for ($i = 0; $i < $tournamentCount; $i++) {
                $tournament = new Tournament();
                $tournament->setName($this->generateTournamentName($faker, $sport));

                // Set dates (start in next 30 days, duration 1-14 days)
                $startDate = $faker->dateTimeBetween('+1 week', '+1 month');
                $tournament->setStartDate($startDate);
                $tournament->setEndDate(
                    $faker->dateTimeBetween(
                        $startDate->format('Y-m-d').' +1 day',
                        $startDate->format('Y-m-d').' +2 weeks'
                    )
                );

                // Assign 4-8 random teams of the same sport
                $eligibleTeams = array_filter(
                    $teamReferences,
                    fn($ref) => $this->getReference($ref, Team::class)->getSport() === $sport->value
                );

                shuffle($eligibleTeams);
                $teamCount = min(rand(4, 8), count($eligibleTeams));
                $selectedTeams = array_slice($eligibleTeams, 0, $teamCount);

                foreach ($selectedTeams as $teamRef) {
                    $tournament->addTeam($this->getReference($teamRef, Team::class));
                }

                $manager->persist($tournament);
                $this->addReference('tournament_'.$sport->name.'_'.$i, $tournament);
            }
        }

        $manager->flush();
    }

    private function generateTournamentName($faker, Sport $sport): string
    {
        $formats = [
            Sport::FOOTBALL->value => [
                '{city} Football Championship',
                '{city} Premier League',
                'International {city} Cup'
            ],
            Sport::BASKETBALL->value => [
                '{city} Basketball Classic',
                '{city} Hoops Tournament',
                '{city} Slam Dunk Challenge'
            ],
            Sport::VOLLEYBALL->value => [
                '{city} Volleyball Open',
                '{city} Spike Masters',
                '{city} Net Championship'
            ]
        ];

        $templates = $formats[$sport->value];
        $template = $templates[array_rand($templates)];

        return str_replace('{city}', $faker->city(), $template);
    }

    public function getDependencies(): array
    {
        return [
            TeamFixtures::class
        ];
    }
}