<?php

namespace App\Controller;

use App\Entity\Injuries;
use App\Form\InjuriesType;
use App\Repository\InjuriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/injuries')]
final class InjuriesController extends AbstractController
{
    #[Route(name: 'app_injuries_index', methods: ['GET'])]
    public function index(InjuriesRepository $injuriesRepository): Response
    {
        return $this->render('injuries/index.html.twig', [
            'injuries' => $injuriesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_injuries_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $injury = new Injuries();
        $form = $this->createForm(InjuriesType::class, $injury);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($injury);
            $entityManager->flush();

            return $this->redirectToRoute('app_injuries_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('injuries/new.html.twig', [
            'injury' => $injury,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_injuries_show', methods: ['GET'])]
    public function show(Injuries $injury): Response
    {
        return $this->render('injuries/show.html.twig', [
            'injury' => $injury,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_injuries_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Injuries $injury, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InjuriesType::class, $injury);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_injuries_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('injuries/edit.html.twig', [
            'injury' => $injury,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_injuries_delete', methods: ['POST'])]
    public function delete(Request $request, Injuries $injury, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$injury->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($injury);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_injuries_index', [], Response::HTTP_SEE_OTHER);
    }
}
