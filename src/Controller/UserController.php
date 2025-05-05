<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\PasswordUpdateType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            // Add flash message
            $this->addFlash('success', 'Profile created successfully!');

            // If not admin, redirect to user show page
            if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->redirectToRoute('app_user_show', ['id' => $user->getId()]);
            }

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $passwordForm = $this->createForm(PasswordUpdateType::class);
        
        $form->handleRequest($request);
        $passwordForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload
            $imageFile = $form->get('profileImage')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/users',
                        $newFilename
                    );
                    $imageUrl = '/uploads/users/'.$newFilename;
                    $user->setImageUrl($imageUrl);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading profile image: ' . $e->getMessage());
                }
            }

            try {
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Profile updated successfully!');

                return $this->redirectToRoute('app_user_show', ['id' => $user->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to update profile: ' . $e->getMessage());
            }
        }

        // Handle password update
        if ($passwordForm->isSubmitted()) {
            // Validate new password
            $newPassword = $passwordForm->get('newPassword')->getData();

            // Validate new password
            if (empty($newPassword)) {
                $this->addFlash('error', 'New password is required');
                return $this->redirectToRoute('app_user_edit', ['id' => $user->getId()]);
            } elseif (strlen($newPassword) < 6) {
                $this->addFlash('error', 'New password must be at least 6 characters long');
                return $this->redirectToRoute('app_user_edit', ['id' => $user->getId()]);
            }

            // Validate password confirmation
            $newPasswordConfirm = $passwordForm->get('newPassword')->get('second')->getData();
            if ($newPassword !== $newPasswordConfirm) {
                $this->addFlash('error', 'New passwords do not match. Please ensure both password fields are identical.');
                return $this->redirectToRoute('app_user_edit', ['id' => $user->getId()]);
            }

            // Hash and set new password
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);

            try {
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Password updated successfully!');
                return $this->redirectToRoute('app_user_show', ['id' => $user->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to update password: ' . $e->getMessage());
                return $this->redirectToRoute('app_user_edit', ['id' => $user->getId()]);
            }
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
            'passwordForm' => $passwordForm,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
