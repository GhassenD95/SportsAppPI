<?php

namespace App\Controller\Admin;

use App\Controller\UserController;
use App\Entity\Equipment;
use App\Entity\Exercise;
use App\Entity\Facility;
use App\Entity\Injuries;
use App\Entity\MatchEvent;
use App\Entity\MedicalReport;
use App\Entity\PlayerPerformance;
use App\Entity\Team;
use App\Entity\TeamPerformance;
use App\Entity\Tournament;
use App\Entity\Training;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function index(): Response
    {
        // Collect statistics
        $totalUsers = $this->entityManager->getRepository(User::class)->count([]);
        $totalTeams = $this->entityManager->getRepository(Team::class)->count([]);
        $totalFacilities = $this->entityManager->getRepository(Facility::class)->count([]);
        $totalTournaments = $this->entityManager->getRepository(Tournament::class)->count([]);
        $totalTrainings = $this->entityManager->getRepository(Training::class)->count([]);
        $totalMatches = $this->entityManager->getRepository(MatchEvent::class)->count([]);

        // Render the dashboard view directly
        return $this->render('admin/dashboard.html.twig', [
            'total_users' => $totalUsers,
            'total_teams' => $totalTeams,
            'total_facilities' => $totalFacilities,
            'total_tournaments' => $totalTournaments,
            'total_trainings' => $totalTrainings,
            'total_matches' => $totalMatches,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('SportsAppPI');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Users', 'fas fa-list', User::class);
        yield MenuItem::linkToCrud('Teams', 'fas fa-list', Team::class);
        yield MenuItem::linkToCrud('Facilities', 'fas fa-list', Facility::class);
        yield MenuItem::linkToCrud('Equipment', 'fas fa-list', Equipment::class);
        yield MenuItem::linkToCrud('Tournaments', 'fas fa-list', Tournament::class);
        yield MenuItem::linkToCrud('Matches', 'fas fa-list', MatchEvent::class);
        yield MenuItem::linkToCrud('Exercises', 'fas fa-list', Exercise::class);
        yield MenuItem::linkToCrud('Training', 'fas fa-list', Training::class);
        yield MenuItem::linkToCrud('Injuries', 'fas fa-list', Injuries::class);
        yield MenuItem::linkToCrud('Medical Reports', 'fas fa-file-medical', MedicalReport::class);
        yield MenuItem::linkToCrud('Player Performance', 'fas fa-running', PlayerPerformance::class);
        yield MenuItem::linkToCrud('Team Performance', 'fas fa-users', TeamPerformance::class);
        
        // Add logout menu item at the bottom
        yield MenuItem::section();
        yield MenuItem::linkToRoute('Logout', 'fa fa-sign-out', 'app_logout');
    }
}
