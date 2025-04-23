<?php

namespace App\Controller;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use App\Form\PasswordResetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class PasswordResetController extends AbstractController
{
    #[Route('/password/reset/{token}', name: 'app_password_reset')]
    public function index(
        EntityManagerInterface $entityManager,
        string $token,
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $tokenEntity = $entityManager->getRepository(PasswordResetToken::class)
            ->findOneBy(["token" => $token]);

        if (!$tokenEntity || $tokenEntity->getExpiresAt() < new \DateTimeImmutable()) {
            $this->addFlash('warning', 'Invalid or expired token');
            return $this->redirectToRoute('app_login');
        }

        $user = $tokenEntity->getUser();
        $form = $this->createForm(PasswordResetType::class, $user); // Pass the actual user
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get the password from plainPassword field
            $user->setPassword(
                $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()) // Changed from 'password')
            );
            $entityManager->remove($tokenEntity);
            $entityManager->flush();

            $this->addFlash('success', 'Password updated successfully');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('password_reset/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}