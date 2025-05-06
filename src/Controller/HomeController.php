<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\InjuriesRepository;
use App\Repository\MatchEventRepository;
use App\Repository\PlayerPerformanceRepository;
use App\Repository\TeamRepository;
use App\Repository\TrainingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    private TeamRepository $teamRepository;
    private TrainingRepository $trainingRepository;
    private MatchEventRepository $matchEventRepository;
    private PlayerPerformanceRepository $playerPerformanceRepository;
    private InjuriesRepository $injuriesRepository;

    public function __construct(
        TeamRepository $teamRepository,
        TrainingRepository $trainingRepository,
        MatchEventRepository $matchEventRepository,
        PlayerPerformanceRepository $playerPerformanceRepository,
        InjuriesRepository $injuriesRepository
    ) {
        $this->teamRepository = $teamRepository;
        $this->trainingRepository = $trainingRepository;
        $this->matchEventRepository = $matchEventRepository;
        $this->playerPerformanceRepository = $playerPerformanceRepository;
        $this->injuriesRepository = $injuriesRepository;
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Redirect admin users to the admin dashboard
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin'); // Assuming 'admin' is the route name for EasyAdminBundle
        }

        // Prepare dashboard data based on user role
        $dashboardData = [];

        if ($this->isGranted('ROLE_ATHLETE') && !$this->isGranted('ROLE_COACH') && !$this->isGranted('ROLE_MANAGER')) {
            $dashboardData = $this->getAthleteDashboardData($user);
        } elseif ($this->isGranted('ROLE_COACH')) {
            // Placeholder for Coach Data Fetching - Keep previous logic if needed
            // $dashboardData = $this->getCoachDashboardData($user);
        } elseif ($this->isGranted('ROLE_MANAGER')) {
            // Placeholder for Manager Data Fetching - Keep previous logic if needed
            // $dashboardData = $this->getManagerDashboardData($user);
        }

        // Render dashboard
        return $this->render('home/index.html.twig', array_merge(
            $dashboardData,
            [
                'user_role' => $user->getRoles(), // Pass all roles
                'user_email' => $user->getEmail()
            ]
        ));
    }

    private function getAthleteDashboardData(User $user): array
    {
        $team = $user->getTeam();
        $athleteId = $user->getId();
        $teamId = $team ? $team->getId() : null;

        $upcomingTraining = null;
        $upcomingMatch = null;

        if ($teamId) {
            // Assuming findUpcomingByTeam methods exist or will be added
            // These should fetch the *next* one session/match for the team
            $upcomingTraining = $this->trainingRepository->findOneUpcomingByTeam($teamId);
            $upcomingMatch = $this->matchEventRepository->findOneUpcomingByTeam($teamId);
        }

        // Assuming findLatestByPlayer and findActiveByPlayer methods exist or will be added
        $latestPerformance = $this->playerPerformanceRepository->findLatestByPlayer($athleteId);
        $activeInjuries = $this->injuriesRepository->findActiveByPlayer($athleteId);

        return [
            'team_details' => $team,
            'upcoming_training' => $upcomingTraining,
            'upcoming_match' => $upcomingMatch,
            'latest_performance' => $latestPerformance,
            'active_injuries' => $activeInjuries,
            'team_id' => $teamId, // Pass the already fetched team ID
        ];
    }

    // Placeholder for getCoachDashboardData - Add back previous implementation if needed
    /*
    private function getCoachDashboardData(User $user): array
    {
        // ... implementation from previous session ...
        return [];
    }
    */

    // Placeholder for getManagerDashboardData - Add back previous implementation if needed
    /*
    private function getManagerDashboardData(User $user): array
    {
        // ... implementation from previous session ...
        return [];
    }
    */
}
