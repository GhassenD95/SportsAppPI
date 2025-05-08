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
            $dashboardData = $this->getCoachDashboardData($user);
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
        $team = $user->getTeam(); // This seems to fetch a single team, an athlete might be in multiple via User->getTeams()
        $athleteId = $user->getId();
        // $teamId = $team ? $team->getId() : null; // Not directly used for new training queries

        // Use new methods from TrainingRepository
        $upcomingTrainings = $this->trainingRepository->findUpcomingByAthlete($user, 5);
        $trainingsThisMonthCount = $this->trainingRepository->countTrainingsThisMonthByAthlete($user);

        // Existing logic for upcoming match (assuming it's singular and based on one primary team or different logic)
        $upcomingMatch = null;
        if ($team) { // If relying on a single primary team concept for matches
            $upcomingMatch = $this->matchEventRepository->findOneUpcomingByTeam($team->getId());
        }

        $latestPerformance = $this->playerPerformanceRepository->findLatestByPlayer($athleteId);
        $activeInjuries = $this->injuriesRepository->findActiveByPlayer($athleteId);

        return [
            'team_details' => $team, // Keep for other parts of the dashboard if needed
            'athlete_upcoming_trainings' => $upcomingTrainings,
            'athlete_trainings_this_month_count' => $trainingsThisMonthCount,
            'upcoming_match' => $upcomingMatch,
            'latest_performance' => $latestPerformance,
            'active_injuries' => $activeInjuries,
            // 'team_id' => $teamId, // No longer strictly needed for the new training queries here
        ];
    }

    private function getCoachDashboardData(User $coach): array
    {
        $upcomingTrainings = $this->trainingRepository->findUpcomingByCoach($coach, 5);
        $trainingsThisMonthCount = $this->trainingRepository->countTrainingsThisMonthByCoach($coach);
        $topExercisesRaw = $this->trainingRepository->findExercisesByCoachWithUsageCount($coach, 5);

        $topExercisesLabels = [];
        $topExercisesData = [];
        foreach ($topExercisesRaw as $exerciseStat) {
            $topExercisesLabels[] = $exerciseStat['name'];
            $topExercisesData[] = $exerciseStat['usageCount'];
        }

        return [
            'coach_upcoming_trainings' => $upcomingTrainings,
            'coach_trainings_this_month_count' => $trainingsThisMonthCount,
            'coach_top_exercises_labels' => $topExercisesLabels,
            'coach_top_exercises_data' => $topExercisesData,
        ];
    }

    // Placeholder for getManagerDashboardData - Add back previous implementation if needed
    /*
    private function getManagerDashboardData(User $user): array
    {
        // ... implementation from previous session ...
        return [];
    }
    */
}
