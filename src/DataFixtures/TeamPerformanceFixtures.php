<?php

namespace App\DataFixtures;

use App\Entity\TeamPerformance;
use App\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TeamPerformanceFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['performance'];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Get all teams using references
        $teams = [];
        for ($i = 0; $i < 5; $i++) { // Assuming we have 5 teams from TeamFixtures
            try {
                $teams[] = $this->getReference('team-' . $i, Team::class);
            } catch (\Exception $e) {
                // Stop collecting teams if reference doesn't exist
                break;
            }
        }

        if (empty($teams)) {
            throw new \RuntimeException('No teams found. Make sure TeamFixtures is loaded first.');
        }

        // Create performances for each team (5-8 performances per team)
        foreach ($teams as $team) {
            $numPerformances = $faker->numberBetween(5, 8);
            
            for ($i = 0; $i < $numPerformances; $i++) {
                $performance = new TeamPerformance();
                
                // Set basic information
                $performance->setTeam($team);
                $performance->setPerformanceDate($faker->dateTimeBetween('-6 months', 'now'));
                
                // Set performance metrics
                $performance->setGoalsScored($faker->numberBetween(0, 5));
                $performance->setGoalsConceded($faker->numberBetween(0, 4));
                $performance->setShotsOnTarget($faker->numberBetween(5, 15));
                $performance->setShotsConceded($faker->numberBetween(3, 12));
                $performance->setPossessionPercentage($faker->numberBetween(35, 65));
                $performance->setPassesCompleted($faker->numberBetween(200, 600));
                $performance->setTackles($faker->numberBetween(10, 30));
                $performance->setInterceptions($faker->numberBetween(8, 25));
                $performance->setFouls($faker->numberBetween(5, 15));
                $performance->setYellowCards($faker->numberBetween(0, 4));
                $performance->setRedCards($faker->numberBetween(0, 1));
                
                // Set rating (1-10 with one decimal place)
                $performance->setRating((string)$faker->randomFloat(1, 5, 10));
                
                // Add optional notes
                if ($faker->boolean(70)) {
                    $performance->setNotes($faker->text(200));
                }
                
                $manager->persist($performance);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TeamFixtures::class,
        ];
    }
} 