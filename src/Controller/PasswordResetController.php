<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

final class PasswordResetController extends AbstractController
{
    #[Route('/password/reset', name: 'app_password_reset')]
    public function index(): Response
    {
        return $this->render('password_reset/index.html.twig', [
            'controller_name' => 'PasswordResetController',
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/test-email')]
    public function testEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('test@yoursportsapp.com')
            ->to('test@example.com')
            ->subject('MailHog Test')
            ->text('This should appear in MailHog!');

        $mailer->send($email);
        return new Response('Email sent. Check MailHog at http://localhost:8025');
    }
}