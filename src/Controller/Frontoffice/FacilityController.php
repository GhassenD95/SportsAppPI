<?php

namespace App\Controller\Frontoffice;

use App\Repository\FacilityRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/frontoffice/facility')]
class FacilityController extends BaseController
{
    #[Route('/', name: 'frontoffice_facility_index', methods: ['GET'])]
    public function index(FacilityRepository $facilityRepository): Response
    {
        return $this->renderFrontoffice('facility/index.html.twig', [
            'facilities' => $facilityRepository->findAll(),
        ]);
    }
} 