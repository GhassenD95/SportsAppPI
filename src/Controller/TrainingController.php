<?php

namespace App\Controller;

use App\Entity\Training;
use App\Form\TrainingType;
use App\Repository\FacilityRepository;
use App\Repository\TeamRepository;
use App\Repository\TrainingRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use DateTime;

#[Route('/training')]
final class TrainingController extends AbstractController
{
    #[Route(name: 'app_training_index', methods: ['GET'])]
    public function index(
        Request $request,
        TrainingRepository $trainingRepository,
        UserRepository $userRepository,
        TeamRepository $teamRepository,
        FacilityRepository $facilityRepository
    ): Response {
        $coachId = $request->query->get('coach');
        $teamId = $request->query->get('team');
        $facilityId = $request->query->get('facility');
        $startDateString = $request->query->get('start_date');
        $endDateString = $request->query->get('end_date');

        $coachFilter = $coachId ? $userRepository->find($coachId) : null;
        $teamFilter = $teamId ? $teamRepository->find($teamId) : null;
        $facilityFilter = $facilityId ? $facilityRepository->find($facilityId) : null;
        
        $startDateFilter = null;
        if ($startDateString) {
            try {
                $startDateFilter = new DateTime($startDateString);
            } catch (\Exception $e) {
                // Optional: Add a flash message or log error if date is invalid
                $startDateFilter = null;
            }
        }

        $endDateFilter = null;
        if ($endDateString) {
            try {
                $endDateFilter = new DateTime($endDateString);
            } catch (\Exception $e) {
                // Optional: Add a flash message or log error if date is invalid
                $endDateFilter = null;
            }
        }

        $trainings = $trainingRepository->findByFilters(
            $coachFilter, 
            $teamFilter, 
            $facilityFilter,
            $startDateFilter,
            $endDateFilter
        );

        $allCoaches = $userRepository->findByRole('ROLE_COACH', ['name' => 'ASC']);
        $allTeams = $teamRepository->findBy([], ['name' => 'ASC']);
        $allFacilities = $facilityRepository->findBy([], ['name' => 'ASC']);

        return $this->render('training/index.html.twig', [
            'trainings' => $trainings,
            'coaches' => $allCoaches,
            'teams' => $allTeams,
            'facilities' => $allFacilities,
            'selectedCoachId' => $coachId,
            'selectedTeamId' => $teamId,
            'selectedFacilityId' => $facilityId,
            'selectedStartDate' => $startDateString, // Pass original string back
            'selectedEndDate' => $endDateString,   // Pass original string back
        ]);
    }

    #[Route('/new', name: 'app_training_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $training = new Training();
        $user = $this->getUser(); // Get the logged-in user
        $formOptions = [];

        // If the user is a coach, pre-fill the coach field and remove it from the form
        if ($user && in_array('ROLE_COACH', $user->getRoles())) {
            $training->setCoach($user);
            // It's generally better to handle form field removal/modification within the form type itself based on options,
            // but for a quick conditional removal here, we can modify the form after creation or pass an option.
            // For simplicity here, we'll modify the form directly if it's built.
            // A more robust way is to pass an option to TrainingType and handle it there.
        }

        $form = $this->createForm(TrainingType::class, $training, $formOptions);

        // If the user is a coach, remove the coach field from the already created form view
        if ($user && in_array('ROLE_COACH', $user->getRoles()) && $form->has('coach')) {
            $form->remove('coach');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($training);
            $entityManager->flush();

            $this->addFlash('success', 'Training session successfully created.');

            return $this->redirectToRoute('app_training_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('training/new.html.twig', [
            'training' => $training,
            'form' => $form->createView(), // Ensure we pass the form view
        ]);
    }

    #[Route('/{id}', name: 'app_training_show', methods: ['GET'])]
    public function show(Training $training): Response
    {
        return $this->render('training/show.html.twig', [
            'training' => $training,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_training_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Training $training, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TrainingType::class, $training);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Training session successfully updated.');

            return $this->redirectToRoute('app_training_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('training/edit.html.twig', [
            'training' => $training,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_training_delete', methods: ['POST'])]
    public function delete(Request $request, Training $training, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$training->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($training);
            $entityManager->flush();

            $this->addFlash('success', 'Training session successfully deleted.');
        }

        return $this->redirectToRoute('app_training_index', [], Response::HTTP_SEE_OTHER);
    }
}
