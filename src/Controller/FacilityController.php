<?php

namespace App\Controller;

use App\Entity\Facility;
use App\Form\FacilityType;
use App\Repository\FacilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Enum\Sport;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/facility')]
final class FacilityController extends AbstractController
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    #[Route(name: 'app_facility_index', methods: ['GET'])]
    public function index(Request $request, FacilityRepository $facilityRepository): Response
    {
        $name = $request->query->get('name');
        $location = $request->query->get('location');
        $sports = $request->query->all('sports'); // Get all sports as an array
        $managedByMe = $request->query->getBoolean('managed_by_me'); // Get as boolean
        $page = $request->query->getInt('page', 1);
        $limit = 9; // Or get from request/configuration

        $currentUser = null;
        if ($managedByMe && $this->getUser() && $this->isGranted('ROLE_MANAGER')) {
            $currentUser = $this->getUser();
        }

        $facilitiesPaginator = $facilityRepository->findByFilters($name, $location, $sports, $managedByMe, $currentUser, $page, $limit);

        return $this->render('facility/index.html.twig', [
            'facilities' => $facilitiesPaginator,
            'all_sports' => Sport::choices(), // Pass sport choices for the filter dropdown
            'current_filters' => [
                'name' => $name,
                'location' => $location,
                'sports' => $sports,
                'managed_by_me' => $managedByMe,
            ],
            'currentPage' => $page,
            'totalPages' => ceil($facilitiesPaginator->count() / $limit),
            'limit' => $limit
        ]);
    }

    #[Route('/new', name: 'app_facility_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $facility = new Facility();
        // Set the manager to the currently logged-in user
        $manager = $this->getUser();
        if (!$manager) {
            $this->addFlash('error', 'You must be logged in to create a facility.');
            return $this->redirectToRoute('app_login'); // Or your login route
        }
        $facility->setManager($manager);

        $form = $this->createForm(FacilityType::class, $facility);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('facilities_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $this->addFlash('error', 'Failed to upload image: '.$e->getMessage());
                    // Optionally, re-render the form or redirect
                    return $this->render('facility/new.html.twig', [
                        'facility' => $facility,
                        'form' => $form->createView(), // Use createView() for re-rendering
                    ]);
                }
                $facility->setImageUrl($newFilename);
            }

            $entityManager->persist($facility);
            $entityManager->flush();
            
            $this->addFlash('success', 'Facility created successfully!');
            return $this->redirectToRoute('app_facility_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('facility/new.html.twig', [
            'facility' => $facility,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_facility_show', methods: ['GET'])]
    public function show(Facility $facility): Response
    {
        return $this->render('facility/show.html.twig', [
            'facility' => $facility,
            'all_sports' => Sport::choices(), // Pass sport choices
        ]);
    }

    #[Route('/{id}/edit', name: 'app_facility_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Facility $facility, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FacilityType::class, $facility);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                // TODO: Optionally delete the old image if it exists and is different
                // $oldImage = $facility->getImageUrl();
                // if ($oldImage && file_exists($this->getParameter('facilities_directory').'/'.$oldImage)) {
                //     unlink($this->getParameter('facilities_directory').'/'.$oldImage);
                // }

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('facilities_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload image: '.$e->getMessage());
                    return $this->render('facility/edit.html.twig', [
                        'facility' => $facility,
                        'form' => $form->createView(),
                    ]);
                }
                $facility->setImageUrl($newFilename);
            }
            // Manager should not be changed here typically, or only by an admin.
            // If the manager was set during creation and is intended to be fixed,
            // ensure it's not re-set or is handled with specific logic if it needs to be editable.

            $entityManager->flush();
            $this->addFlash('success', 'Facility updated successfully!');
            return $this->redirectToRoute('app_facility_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('facility/edit.html.twig', [
            'facility' => $facility,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_facility_delete', methods: ['POST'])]
    public function delete(Request $request, Facility $facility, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$facility->getId(), $request->getPayload()->getString('_token'))) {
            // TODO: Optionally delete the associated image file
            // $image = $facility->getImageUrl();
            // if ($image && file_exists($this->getParameter('facilities_directory').'/'.$image)) {
            //     unlink($this->getParameter('facilities_directory').'/'.$image);
            // }
            $entityManager->remove($facility);
            $entityManager->flush();
            $this->addFlash('success', 'Facility deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('app_facility_index', [], Response::HTTP_SEE_OTHER);
    }
}
