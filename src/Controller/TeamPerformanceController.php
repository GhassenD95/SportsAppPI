<?php

namespace App\Controller;

use App\Entity\TeamPerformance;
use App\Form\TeamPerformanceType;
use App\Repository\TeamPerformanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/team/performance')]
final class TeamPerformanceController extends AbstractController
{
    #[Route(name: 'app_team_performance_index', methods: ['GET'])]
    public function index(TeamPerformanceRepository $teamPerformanceRepository): Response
    {
        return $this->render('team_performance/index.html.twig', [
            'team_performances' => $teamPerformanceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_team_performance_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $teamPerformance = new TeamPerformance();
        $form = $this->createForm(TeamPerformanceType::class, $teamPerformance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($teamPerformance);
            $entityManager->flush();

            return $this->redirectToRoute('app_team_performance_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('team_performance/new.html.twig', [
            'team_performance' => $teamPerformance,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_team_performance_show', methods: ['GET'])]
    public function show(TeamPerformance $teamPerformance): Response
    {
        return $this->render('team_performance/show.html.twig', [
            'team_performance' => $teamPerformance,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_team_performance_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TeamPerformance $teamPerformance, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TeamPerformanceType::class, $teamPerformance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_team_performance_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('team_performance/edit.html.twig', [
            'team_performance' => $teamPerformance,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_team_performance_delete', methods: ['POST'])]
    public function delete(Request $request, TeamPerformance $teamPerformance, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$teamPerformance->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($teamPerformance);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_team_performance_index', [], Response::HTTP_SEE_OTHER);
    }
}
