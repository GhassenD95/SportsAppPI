<?php

namespace App\DataFixtures;

use App\Entity\Exercise;
use App\Service\ExerciseApiService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ExerciseFixtures extends Fixture
{
    public function __construct(
        private ExerciseApiService $exerciseApi,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir
    ) {}

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
            $apiExercises = $this->exerciseApi->getAllExerciseNames();
            $uploadDir = $this->projectDir.'/public/uploads/exercises';

            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $exercisesToCreate = min(30, count($apiExercises));
            $createdCount = 0;

            foreach ($apiExercises as $ex) {
                if ($createdCount >= $exercisesToCreate) {
                    break;
                }

                $existing = $manager->getRepository(Exercise::class)
                    ->findOneBy(['apiId' => $ex['id']]);

                if ($existing) {
                    continue;
                }

                try {
                    $exerciseData = $this->exerciseApi->fetchExerciseDetails($ex['id'], $uploadDir);

                    $exercise = new Exercise();
                    $exercise->setName($exerciseData['name']);
                    $exercise->setTarget($exerciseData['target']);
                    $exercise->setApiId($exerciseData['id']);
                    $exercise->setInstructions(implode("\n", $exerciseData['instructions'] ?? []));

                    // Extract only the filename from the URL
                    if ($exerciseData['gifUrl']) {
                        $pathParts = pathinfo($exerciseData['gifUrl']);
                        $exercise->setImageUrl($pathParts['basename']);
                    } else {
                        $exercise->setImageUrl(null);
                    }

                    $manager->persist($exercise);
                    $createdCount++;

                } catch (\Exception $e) {
                    continue;
                }
            }

            $manager->flush();

        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to load exercises from API: ' . $e->getMessage());
        }
    }
}