<?php

namespace App\DataFixtures;

use App\Entity\Injuries;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class InjuriesFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    private const INJURY_TYPES = [
        'Sprain',
        'Strain',
        'Fracture',
        'Dislocation',
        'Contusion',
        'Tendonitis',
        'Concussion',
        'Muscle tear',
        'Ligament tear',
        'Bruise'
    ];

    private const SEVERITY_LEVELS = [
        'Mild',
        'Moderate',
        'Severe'
    ];

    private const STATUS_OPTIONS = [
        'Active',
        'Recovering',
        'Recovered',
        'Chronic'
    ];

    public static function getGroups(): array
    {
        return ['injuries'];
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

        // Create injuries for each athlete (1-2 injuries per athlete)
        foreach ($athletes as $athlete) {
            $numInjuries = $faker->numberBetween(1, 2);
            
            for ($i = 0; $i < $numInjuries; $i++) {
                $injury = new Injuries();
                
                // Set team (use athlete's team if available, or random team)
                $team = $faker->randomElement($teams);
                
                // Set basic information
                $injury->setPlayer($athlete);
                $injury->setTeam($team);
                $injury->setInjuryDate($faker->dateTimeBetween('-6 months', 'now'));
                $injury->setInjuryType($faker->randomElement(self::INJURY_TYPES));
                $injury->setSeverity($faker->randomElement(self::SEVERITY_LEVELS));
                
                // Set dates
                $injuryDate = $injury->getInjuryDate();
                $expectedRecoveryDate = clone $injuryDate;
                $expectedRecoveryDate->modify('+' . $faker->numberBetween(1, 12) . ' weeks');
                $injury->setExpectedRecoveryDate($expectedRecoveryDate);
                
                // Set actual recovery date (70% chance)
                if ($faker->boolean(70)) {
                    $actualRecoveryDate = clone $expectedRecoveryDate;
                    $actualRecoveryDate->modify('+' . $faker->numberBetween(-1, 4) . ' weeks');
                    $injury->setActualRecoveryDate($actualRecoveryDate);
                }
                
                // Set description and treatment plan
                $injury->setDescription($faker->text(200));
                $injury->setTreatmentPlan($faker->text(300));
                
                // Set status
                $injury->setStatus($faker->randomElement(self::STATUS_OPTIONS));
                
                // Add optional notes
                $injury->setNotes($faker->optional(0.7)->text(200));
                
                $manager->persist($injury);
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