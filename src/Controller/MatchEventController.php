<?php

namespace App\Controller;

use App\Entity\MatchEvent;
use App\Form\MatchEventType;
use App\Repository\MatchEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/match/event')]
final class MatchEventController extends AbstractController
{
    #[Route(name: 'app_match_event_index', methods: ['GET'])]
    public function index(MatchEventRepository $matchEventRepository): Response
    {
        return $this->render('match_event/index.html.twig', [
            'match_events' => $matchEventRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_match_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $matchEvent = new MatchEvent();
        $form = $this->createForm(MatchEventType::class, $matchEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($matchEvent);
            $entityManager->flush();

            return $this->redirectToRoute('app_match_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('match_event/new.html.twig', [
            'match_event' => $matchEvent,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_match_event_show', methods: ['GET'])]
    public function show(MatchEvent $matchEvent): Response
    {
        return $this->render('match_event/show.html.twig', [
            'match_event' => $matchEvent,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_match_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MatchEvent $matchEvent, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MatchEventType::class, $matchEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_match_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('match_event/edit.html.twig', [
            'match_event' => $matchEvent,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_match_event_delete', methods: ['POST'])]
    public function delete(Request $request, MatchEvent $matchEvent, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$matchEvent->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($matchEvent);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_match_event_index', [], Response::HTTP_SEE_OTHER);
    }
}
