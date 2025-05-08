<?php

namespace App\Controller;

use App\Entity\Exercise;
use App\Form\ExerciseType;
use App\Repository\ExerciseRepository;
use App\Service\ExerciseApiService;
use App\Service\YouTubeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/exercise')]
final class ExerciseController extends AbstractController
{
    private ExerciseApiService $exerciseApiService;
    private string $exercisesUploadDir;

    public function __construct(ExerciseApiService $exerciseApiService, string $kernelProjectDir)
    {
        $this->exerciseApiService = $exerciseApiService;
        // Ensure the path is correct and consistently used, relative to public for URLs
        $this->exercisesUploadDir = $kernelProjectDir . '/public/uploads/exercises'; 
    }

    #[Route(name: 'app_exercise_index', methods: ['GET'])]
    public function index(ExerciseRepository $exerciseRepository): Response
    {
        return $this->render('exercise/index.html.twig', [
            'exercises' => $exerciseRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_exercise_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $exercise = new Exercise();
        $form = $this->createForm(ExerciseType::class, $exercise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $apiId = $form->get('apiId')->getData();
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($apiId) {
                try {
                    $exerciseDetails = $this->exerciseApiService->fetchExerciseDetails((string)$apiId, $this->exercisesUploadDir);
                    
                    $exercise->setName($exerciseDetails['name'] ?? $exercise->getName());
                    $exercise->setTarget($exerciseDetails['target'] ?? $exercise->getTarget());
                    $exercise->setApiId((string)$apiId); // Ensure apiId is stored
                    
                    $instructions = $exerciseDetails['instructions'] ?? [];
                    if (is_array($instructions)) {
                        $exercise->setInstructions(implode("\n", array_filter($instructions)));
                    } elseif (is_string($instructions)) {
                        $exercise->setInstructions($instructions);
                    }

                    // If API provides a gifUrl and no manual file is uploaded, use it.
                    // The service might have already localized it.
                    if (isset($exerciseDetails['gifUrl']) && !$imageFile) {
                        $exercise->setImageUrl($exerciseDetails['gifUrl']);
                    }
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Could not fetch details from API: ' . $e->getMessage());
                    // Decide if we should stop or allow saving with manually entered data
                }
            }

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->exercisesUploadDir,
                        $newFilename
                    );
                    // Store path relative to public directory for web access
                    $exercise->setImageUrl('/uploads/exercises/' . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Could not upload image: ' . $e->getMessage());
                    // Handle error, e.g., don't save image URL or stop process
                }
            }
            
            // Final check: ensure required fields are not empty if no API data was fetched
            // or if API data was incomplete.
            if (empty($exercise->getName()) || empty($exercise->getTarget())) {
                 $this->addFlash('error', 'Exercise name and target muscle cannot be empty.');
                 return $this->render('exercise/new.html.twig', [
                    'exercise' => $exercise,
                    'form' => $form->createView(), // Use createView() here
                ]);
            }

            $entityManager->persist($exercise);
            $entityManager->flush();

            $this->addFlash('success', 'Exercise created successfully!');
            return $this->redirectToRoute('app_exercise_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('exercise/new.html.twig', [
            'exercise' => $exercise,
            'form' => $form->createView(), // Use createView() for rendering
        ]);
    }

    #[Route('/{id}', name: 'app_exercise_show', methods: ['GET'])]
    public function show(Exercise $exercise): Response
    {
        return $this->render('exercise/show.html.twig', [
            'exercise' => $exercise,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_exercise_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Exercise $exercise, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ExerciseType::class, $exercise);
        $originalImageUrl = $exercise->getImageUrl(); // Keep track of old image
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $apiId = $form->get('apiId')->getData();
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('imageFile')->getData();
            $dataChanged = false;

            if ($apiId && $apiId !== $exercise->getApiId()) { // If API ID is selected and different
                try {
                    $exerciseDetails = $this->exerciseApiService->fetchExerciseDetails((string)$apiId, $this->exercisesUploadDir);
                    $exercise->setName($exerciseDetails['name'] ?? $exercise->getName());
                    $exercise->setTarget($exerciseDetails['target'] ?? $exercise->getTarget());
                    $exercise->setApiId((string)$apiId);
                    $instructions = $exerciseDetails['instructions'] ?? [];
                    if (is_array($instructions)) {
                        $exercise->setInstructions(implode("\n", array_filter($instructions)));
                    } elseif (is_string($instructions)) {
                        $exercise->setInstructions($instructions);
                    }
                    if (isset($exerciseDetails['gifUrl'])) {
                        $exercise->setImageUrl($exerciseDetails['gifUrl']); // API image takes precedence on change
                    }
                    $dataChanged = true;
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Could not fetch API details: ' . $e->getMessage());
                }
            }

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move($this->exercisesUploadDir, $newFilename);
                    $exercise->setImageUrl('/uploads/exercises/' . $newFilename);
                    // Potentially delete old image if it was a local file and different from new one
                    // This needs careful handling if originalImageUrl was an API URL.
                    $dataChanged = true;
                } catch (FileException $e) {
                    $this->addFlash('error', 'Could not upload image: ' . $e->getMessage());
                }
            } elseif ($apiId && !$imageFile && isset($exerciseDetails['gifUrl'])) {
                // If API ID was processed and it provided an image, ensure it's set (even if same as original)
                 if ($exercise->getImageUrl() !== $exerciseDetails['gifUrl']) {
                    $exercise->setImageUrl($exerciseDetails['gifUrl']);
                    $dataChanged = true;
                 }
            } elseif (!$apiId && !$imageFile && $form->get('name')->getData() === $exercise->getName() /* ... check other fields */) {
                // If no API id, no file upload, and other fields haven't changed much, 
                // but image might have been cleared or something in a more complex form setup
                // For now, if no new image, and no API providing one, old image remains unless explicitly cleared
            }


            // Final check for edit
            if (empty($exercise->getName()) || empty($exercise->getTarget())) {
                 $this->addFlash('error', 'Exercise name and target muscle cannot be empty.');
                 return $this->render('exercise/edit.html.twig', [
                    'exercise' => $exercise,
                    'form' => $form->createView(),
                ]);
            }
            
            $entityManager->flush();
            $this->addFlash('success', 'Exercise updated successfully!');
            return $this->redirectToRoute('app_exercise_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('exercise/edit.html.twig', [
            'exercise' => $exercise,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_exercise_delete', methods: ['POST'])]
    public function delete(Request $request, Exercise $exercise, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$exercise->getId(), $request->getPayload()->getString('_token'))) {
            // TODO: Optionally delete image file from server if it's a local upload
            $entityManager->remove($exercise);
            $entityManager->flush();
            $this->addFlash('success', 'Exercise deleted successfully!');
        }

        return $this->redirectToRoute('app_exercise_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/youtube-search', name: 'app_exercise_youtube_search', methods: ['GET'])]
    public function youtubeSearch(Exercise $exercise, YouTubeService $youtubeService): JsonResponse
    {
        if (empty($exercise->getName())) {
            return new JsonResponse(['error' => 'Exercise name is missing.'], Response::HTTP_BAD_REQUEST);
        }

        $videos = $youtubeService->searchVideos($exercise->getName() . ' exercise tutorial', 1); // Search for "Exercise Name exercise tutorial"

        if (isset($videos['error'])) {
            // Log the error details if possible, e.g., $this->logger->error('YouTube API Error: ' . $videos['details']);
            return new JsonResponse(['error' => 'Failed to fetch videos from YouTube.', 'details' => $videos['details'] ?? 'No details'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (empty($videos)) {
            return new JsonResponse(['videos' => [], 'message' => 'No videos found.'], Response::HTTP_OK);
        }

        // Return only the first video's ID for simplicity, or you can return the whole list
        return new JsonResponse(['videoId' => $videos[0]['id'] ?? null]);
    }
}
