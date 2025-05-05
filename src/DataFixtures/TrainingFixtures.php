<?php

namespace App\DataFixtures;

use App\Entity\Training;
use App\Entity\TrainingExercise;
use App\Entity\Exercise;
use App\Entity\Team;
use App\Entity\Facility;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TrainingFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \DateMalformedStringException
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Get all available teams using references
        $teams = [];
        for ($i = 0; $i < 6; $i++) {
            try {
                $teams[] = $this->getReference('team-' . $i, Team::class);
            } catch (\Exception $e) {
                break;
            }
        }

        if (empty($teams)) {
            throw new \RuntimeException('No teams found. Please load TeamFixtures first.');
        }

        // Get all exercises
        $exercises = $manager->getRepository(Exercise::class)->findAll();
        if (empty($exercises)) {
            throw new \RuntimeException('No exercises found. Please load ExerciseFixtures first.');
        }

        // Intensity levels
        $intensities = ['Low', 'Medium', 'High'];

        // Determine how many trainings to create per team (2-4)
        $trainingsPerTeam = $faker->numberBetween(2, 4);

        foreach ($teams as $team) {
            for ($i = 0; $i < $trainingsPerTeam; $i++) {
                $training = new Training();

                // Set basic info
                $training->setTitle($this->generateTrainingTitle($faker, $team->getSport()));
                $training->setDescription($faker->paragraph(3));

                // Set time (next 30 days, between 8am and 8pm)
                $startTime = $faker->dateTimeBetween('+1 day', '+30 days');
                $startTime->setTime($faker->numberBetween(8, 19), 0, 0);
                $training->setStartTime(clone $startTime);

                // End time 1-2 hours after start
                $endTime = clone $startTime;
                $endTime->modify('+'.$faker->numberBetween(1, 2).' hours');
                $training->setEndTime($endTime);

                // Set relations
                $training->setTeam($team);
                $training->setCoach($team->getCoach());

                // Set facility - find one that supports the team's sport
                $facility = $this->findSuitableFacility($manager, $team->getSport());
                $training->setFacility($facility);

                // Add exercises (2-5 per training)
                $exerciseCount = $faker->numberBetween(2, min(5, count($exercises)));
                $selectedExercises = $faker->randomElements($exercises, $exerciseCount);

                foreach ($selectedExercises as $exercise) {
                    $trainingExercise = new TrainingExercise();
                    $trainingExercise->setExercise($exercise);
                    $trainingExercise->setDurationMinutes($faker->numberBetween(5, 30));
                    $trainingExercise->setIntensity($faker->randomElement($intensities));
                    $trainingExercise->setNotes($faker->optional(0.7)->sentence()); // 70% chance of having notes

                    $training->addTrainingExercise($trainingExercise);
                }

                $manager->persist($training);
            }
        }

        $manager->flush();
    }

    private function generateTrainingTitle($faker, string $sport): string
    {
        $sportSpecificTitles = [
            'football' => [
                'Tactical Drills',
                'Passing Workshop',
                'Set Piece Practice',
                'Match Simulation'
            ],
            'basketball' => [
                'Shooting Clinic',
                'Ball Handling',
                'Defensive Drills',
                'Fast Break Practice'
            ],
            'volleyball' => [
                'Serving Practice',
                'Blocking Techniques',
                'Reception Drills',
                'Attack Strategies'
            ]
        ];

        $genericTitles = [
            'Team Training',
            'Technical Session',
            'Strength & Conditioning',
            'Recovery Session',
            'Skill Development'
        ];

        // 60% chance for sport-specific title, 40% for generic
        if ($faker->boolean(60) && isset($sportSpecificTitles[$sport])) {
            return $faker->randomElement($sportSpecificTitles[$sport]);
        }

        return $faker->randomElement($genericTitles);
    }

    private function findSuitableFacility(ObjectManager $manager, string $sport): Facility
    {
        // Get all facilities dynamically
        $facilities = [];
        $facilityIndex = 0;
        while (true) {
            try {
                $facility = $this->getReference('facility-' . $facilityIndex, Facility::class);
                $facilities[] = $facility;
                $facilityIndex++;
            } catch (\Exception $e) {
                break;
            }
        }

        if (empty($facilities)) {
            throw new \RuntimeException('No facilities found. Please load FacilityFixtures first.');
        }

        // Filter facilities that support the team's sport
        $suitableFacilities = array_filter($facilities, function(Facility $facility) use ($sport) {
            return in_array($sport, $facility->getSports());
        });

        // If no sport-specific facilities found, use all facilities
        if (empty($suitableFacilities)) {
            $suitableFacilities = $facilities;
        }

        // Return random facility
        return $suitableFacilities[array_rand($suitableFacilities)];
    }

    public function getDependencies(): array
    {
        return [
            TeamFixtures::class,
            FacilityFixtures::class,
            ExerciseFixtures::class
        ];
    }
}