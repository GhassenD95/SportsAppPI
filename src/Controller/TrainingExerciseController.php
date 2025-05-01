<?php

namespace App\Controller;

use App\Entity\TrainingExercise;
use App\Form\TrainingExerciseType;
use App\Repository\TrainingExerciseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/training/exercise')]
final class TrainingExerciseController extends AbstractController
{
    #[Route(name: 'app_training_exercise_index', methods: ['GET'])]
    public function index(TrainingExerciseRepository $trainingExerciseRepository): Response
    {
        return $this->render('training_exercise/index.html.twig', [
            'training_exercises' => $trainingExerciseRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_training_exercise_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $trainingExercise = new TrainingExercise();
        $form = $this->createForm(TrainingExerciseType::class, $trainingExercise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($trainingExercise);
            $entityManager->flush();

            return $this->redirectToRoute('app_training_exercise_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('training_exercise/new.html.twig', [
            'training_exercise' => $trainingExercise,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_training_exercise_show', methods: ['GET'])]
    public function show(TrainingExercise $trainingExercise): Response
    {
        return $this->render('training_exercise/show.html.twig', [
            'training_exercise' => $trainingExercise,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_training_exercise_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TrainingExercise $trainingExercise, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TrainingExerciseType::class, $trainingExercise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_training_exercise_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('training_exercise/edit.html.twig', [
            'training_exercise' => $trainingExercise,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_training_exercise_delete', methods: ['POST'])]
    public function delete(Request $request, TrainingExercise $trainingExercise, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$trainingExercise->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($trainingExercise);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_training_exercise_index', [], Response::HTTP_SEE_OTHER);
    }
}
