<?php

namespace App\Controller\Frontoffice;

use App\Repository\TeamRepository;
use App\Repository\TrainingRepository;
use App\Repository\UserRepository;
use App\Repository\FacilityRepository;
use App\Repository\EquipmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/frontoffice')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_frontoffice_dashboard')]
    #[IsGranted('ROLE_COACH', message: 'Access denied. You must be a coach, manager, or athlete to access this page.')]
    public function index(
        TeamRepository $teamRepository,
        TrainingRepository $trainingRepository,
        UserRepository $userRepository,
        FacilityRepository $facilityRepository,
        EquipmentRepository $equipmentRepository
    ): Response {
        $user = $this->getUser();
        $data = [];

        if ($this->isGranted('ROLE_COACH')) {
            $data['teams_count'] = $teamRepository->count(['coach' => $user]);
            $data['upcoming_trainings_count'] = $trainingRepository->countUpcomingTrainings($user);
            $data['players_count'] = $userRepository->count(['roles' => ['ROLE_ATHLETE']]);
        }

        if ($this->isGranted('ROLE_MANAGER')) {
            $data['facilities_count'] = $facilityRepository->count([]);
            $data['equipment_count'] = $equipmentRepository->count([]);
            $data['maintenance_requests_count'] = 0; // You'll need to implement this
        }

        if ($this->isGranted('ROLE_ATHLETE')) {
            $data['next_training'] = $trainingRepository->findNextTrainingForUser($user);
            $team = $user->getTeam();
            $data['performance_rating'] = $team ? $team->getAverageRating() : null;
        }

        return $this->render('frontoffice/dashboard/index.html.twig', $data);
    }
} 