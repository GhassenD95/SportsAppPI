<?php

namespace App\Controller\Frontoffice;

use App\Repository\TrainingRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/frontoffice/training')]
class TrainingController extends BaseController
{
    #[Route('/', name: 'frontoffice_training_index', methods: ['GET'])]
    public function index(TrainingRepository $trainingRepository): Response
    {
        return $this->renderFrontoffice('training/index.html.twig', [
            'trainings' => $trainingRepository->findAll(),
        ]);
    }
} 