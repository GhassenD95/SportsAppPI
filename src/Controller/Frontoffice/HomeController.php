<?php

namespace App\Controller\Frontoffice;

use App\Repository\MatchEventRepository;
use App\Repository\TrainingRepository;
use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route(['/', '/home'], name: 'app_home')]
    public function index(MatchEventRepository $matchEventRepository, TrainingRepository $trainingRepository, TeamRepository $teamRepository): Response
    {
        // Sample news data - replace with actual data from your database
        $news = [
            [
                'title' => 'New Training Program Launched',
                'content' => 'We are excited to announce our new training program designed to help athletes reach their peak performance.',
                'date' => new \DateTime('2024-05-01'),
                'type' => 'announcement',
                'image' => 'training-program.jpg'
            ],
            [
                'title' => 'Upcoming Championship',
                'content' => 'Don\'t miss our annual championship event. Register now to secure your spot!',
                'date' => new \DateTime('2024-05-15'),
                'type' => 'event',
                'image' => 'championship.jpg'
            ],
            [
                'title' => 'Facility Upgrades',
                'content' => 'Our facilities have been upgraded with new equipment and improved amenities.',
                'date' => new \DateTime('2024-04-20'),
                'type' => 'announcement',
                'image' => 'facility-upgrade.jpg'
            ]
        ];

        return $this->render('frontoffice/home/index.html.twig', [
            'news' => $news
        ]);
    }
} 