<?php

namespace App\Controller;

use App\Entity\PlayerPerformance;
use App\Form\PlayerPerformanceType;
use App\Repository\PlayerPerformanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/player/performance')]
final class PlayerPerformanceController extends AbstractController
{
    #[Route(name: 'app_player_performance_index', methods: ['GET'])]
    public function index(PlayerPerformanceRepository $playerPerformanceRepository): Response
    {
        return $this->render('player_performance/index.html.twig', [
            'player_performances' => $playerPerformanceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_player_performance_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $playerPerformance = new PlayerPerformance();
        $form = $this->createForm(PlayerPerformanceType::class, $playerPerformance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($playerPerformance);
            $entityManager->flush();

            return $this->redirectToRoute('app_player_performance_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('player_performance/new.html.twig', [
            'player_performance' => $playerPerformance,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_player_performance_show', methods: ['GET'])]
    public function show(PlayerPerformance $playerPerformance): Response
    {
        return $this->render('player_performance/show.html.twig', [
            'player_performance' => $playerPerformance,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_player_performance_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PlayerPerformance $playerPerformance, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PlayerPerformanceType::class, $playerPerformance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_player_performance_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('player_performance/edit.html.twig', [
            'player_performance' => $playerPerformance,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_player_performance_delete', methods: ['POST'])]
    public function delete(Request $request, PlayerPerformance $playerPerformance, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$playerPerformance->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($playerPerformance);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_player_performance_index', [], Response::HTTP_SEE_OTHER);
    }
}
