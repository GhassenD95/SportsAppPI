<?php

namespace App\Controller;

use App\Entity\Facility;
use App\Form\FacilityType;
use App\Repository\FacilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/facility')]
final class FacilityController extends AbstractController
{
    #[Route(name: 'app_facility_index', methods: ['GET'])]
    public function index(FacilityRepository $facilityRepository): Response
    {
        return $this->render('facility/index.html.twig', [
            'facilities' => $facilityRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_facility_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $facility = new Facility();
        $form = $this->createForm(FacilityType::class, $facility);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($facility);
            $entityManager->flush();

            return $this->redirectToRoute('app_facility_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('facility/new.html.twig', [
            'facility' => $facility,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_facility_show', methods: ['GET'])]
    public function show(Facility $facility): Response
    {
        return $this->render('facility/show.html.twig', [
            'facility' => $facility,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_facility_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Facility $facility, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FacilityType::class, $facility);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_facility_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('facility/edit.html.twig', [
            'facility' => $facility,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_facility_delete', methods: ['POST'])]
    public function delete(Request $request, Facility $facility, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$facility->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($facility);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_facility_index', [], Response::HTTP_SEE_OTHER);
    }
}
