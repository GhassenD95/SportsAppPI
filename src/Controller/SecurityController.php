<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UserRepository;
use App\Form\PasswordResetVerificationType;
use App\Service\PasswordResetService;
use Doctrine\ORM\EntityManagerInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // If user is already authenticated, redirect to dashboard
        if ($this->getUser()) {
            return $this->redirectToRoute('admin');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): never
    {
        // This method will never be reached if firewall is configured properly
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(
        Request $request, 
        UserRepository $userRepository, 
        PasswordResetService $passwordResetService
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Invalid email format.');
                return $this->render('security/forgot_password.html.twig');
            }

            try {
                // Check if email exists
                $user = $userRepository->findOneBy(['email' => $email]);
                if (!$user) {
                    $this->addFlash('error', 'No account found with this email address.');
                    return $this->render('security/forgot_password.html.twig');
                }

                // Check for verified phones
                $verifiedPhones = $user->getPhones()->filter(function($phone) {
                    return $phone->isVerified() === true;
                });

                if ($verifiedPhones->isEmpty()) {
                    $this->addFlash('error', 'No verified phone number found for this account.');
                    return $this->render('security/forgot_password.html.twig');
                }

                // Initiate password reset
                $result = $passwordResetService->initiatePasswordReset($user);

                if ($result) {
                    $this->addFlash('success', 'A 6-digit reset code has been sent to your verified phone number.');
                    return $this->redirectToRoute('app_verify_reset_token');
                } else {
                    $this->addFlash('error', 'Failed to send reset code. Please contact support.');
                }
            } catch (\Exception $e) {
                // Log the full error for debugging
                error_log('Password reset error: ' . $e->getMessage());
                $this->addFlash('error', 'An unexpected error occurred. Please try again or contact support.');
            }
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route(path: '/verify-reset-token', name: 'app_verify_reset_token')]
    public function verifyResetToken(
        Request $request, 
        UserRepository $userRepository, 
        PasswordResetService $passwordResetService,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(PasswordResetVerificationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $email = $request->getSession()->get('reset_email');
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user && $passwordResetService->validateResetToken($user, $data['resetToken'])) {
                // Hash new password
                $hashedPassword = $passwordHasher->hashPassword($user, $data['plainPassword']);
                $user->setPassword($hashedPassword);
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Password successfully reset.');
                return $this->redirectToRoute('app_login');
            }

            $this->addFlash('error', 'Invalid reset token.');
        }

        return $this->render('password_reset/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
