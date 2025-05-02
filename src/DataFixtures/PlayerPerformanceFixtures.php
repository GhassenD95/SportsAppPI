<?php

namespace App\DataFixtures;

use App\Entity\PlayerPerformance;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PlayerPerformanceFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['performance'];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Get all athletes using references
        $athletes = [];
        for ($i = 0; $i < 20; $i++) {
            try {
                $athletes[] = $this->getReference('athlete-' . $i, User::class);
            } catch (\Exception $e) {
                // Stop collecting athletes if reference doesn't exist
                break;
            }
        }

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

        if (empty($athletes) || empty($teams)) {
            throw new \RuntimeException('No athletes or teams found. Make sure UserFixtures and TeamFixtures are loaded first.');
        }

        // Create performances for each athlete (3-5 performances per athlete)
        foreach ($athletes as $athlete) {
            $numPerformances = $faker->numberBetween(3, 5);
            
            for ($i = 0; $i < $numPerformances; $i++) {
                $performance = new PlayerPerformance();
                
                // Set team (use athlete's team if available, or random team)
                $team = $athlete->getTeam() ?? $faker->randomElement($teams);
                
                // Set basic information
                $performance->setPlayer($athlete);
                $performance->setTeam($team);
                $performance->setPerformanceDate($faker->dateTimeBetween('-6 months', 'now'));
                
                // Set performance metrics
                $performance->setGoalsScored($faker->numberBetween(0, 3));
                $performance->setAssists($faker->numberBetween(0, 2));
                $performance->setMinutesPlayed($faker->numberBetween(45, 90));
                $performance->setShotsOnTarget($faker->numberBetween(1, 5));
                $performance->setPassesCompleted($faker->numberBetween(20, 50));
                $performance->setTackles($faker->numberBetween(1, 8));
                $performance->setInterceptions($faker->numberBetween(1, 5));
                $performance->setSaves($faker->numberBetween(0, 5)); // More for goalkeepers
                
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
            UserFixtures::class,
            TeamFixtures::class,
        ];
    }
} 