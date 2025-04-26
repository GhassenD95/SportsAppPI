<?php

namespace App\DataFixtures;

use App\Entity\Exercise;
use App\Service\ExerciseApiService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ExerciseFixtures extends Fixture
{
    public function __construct(private ExerciseApiService $exerciseApi) {}

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function load(ObjectManager $manager): void
    {
        try {
            // Get all exercises from API
            $apiExercises = $this->exerciseApi->getAllExerciseNames();

            // Limit to 30 unique exercises
            $exercisesToCreate = min(30, count($apiExercises));
            $createdCount = 0;

            foreach ($apiExercises as $ex) {
                if ($createdCount >= $exercisesToCreate) {
                    break;
                }

                // Skip if we already have an exercise with this API ID
                $existing = $manager->getRepository(Exercise::class)
                    ->findOneBy(['apiId' => $ex['id']]);

                if ($existing) {
                    continue;
                }

                try {
                    // Fetch full exercise details
                    $exerciseData = $this->exerciseApi->fetchExerciseDetails($ex['id']);

                    // Create new Exercise entity
                    $exercise = new Exercise();
                    $exercise->setName($exerciseData['name']);
                    $exercise->setTarget($exerciseData['target']);
                    $exercise->setApiId($exerciseData['id']);
                    $exercise->setInstructions(implode("\n", $exerciseData['instructions'] ?? []));
                    $exercise->setImageUrl($exerciseData['gifUrl'] ?? null);

                    $manager->persist($exercise);
                    $createdCount++;

                } catch (\Exception $e) {
                    // Skip if we can't fetch details for this exercise
                    continue;
                }
            }

            $manager->flush();

        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to load exercises from API: ' . $e->getMessage());
        }
    }
}