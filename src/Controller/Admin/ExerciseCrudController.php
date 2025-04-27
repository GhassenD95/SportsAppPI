<?php

namespace App\Controller\Admin;

use App\Entity\Exercise;
use App\Service\ExerciseApiService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ExerciseCrudController extends AbstractCrudController
{
    private string $exerciseImageDirectory;
    private const UPLOAD_PATH = '/uploads/exercises/';

    public function __construct(
        private readonly ExerciseApiService $exerciseApi,
        string $projectDir
    ) {
        $this->exerciseImageDirectory = $projectDir . '/public' . self::UPLOAD_PATH;

        // Create directory if it doesn't exist
        if (!file_exists($this->exerciseImageDirectory)) {
            mkdir($this->exerciseImageDirectory, 0777, true);
        }
    }

    public static function getEntityFqcn(): string
    {
        return Exercise::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('detail', fn (Exercise $exercise) => (string) $exercise->getName())
            ->setEntityLabelInSingular('Exercise')
            ->setEntityLabelInPlural('Exercises');
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();
        yield IntegerField::new('apiId', 'API ID')->onlyOnIndex();
        yield TextField::new('name');
        yield TextField::new('target');
        yield TextareaField::new('instructions')->setRequired(false);

        if (Crud::PAGE_INDEX === $pageName || Crud::PAGE_DETAIL === $pageName) {
            yield ImageField::new('imageUrl')
                ->setBasePath(self::UPLOAD_PATH);
        } else {
            yield ImageField::new('imageUrl')
                ->setBasePath('')
                ->setUploadDir('public/uploads/exercises')
                ->setFormTypeOptions([
                    'attr' => [
                        'accept' => 'image/*',
                    ],
                ])
                ->setRequired(false);
            // No setUploadedFileNamePattern or setVirtualDir needed here for API image name saving
            $apiExercises = $this->exerciseApi->getAllExerciseNames();
            $choices = [];
            foreach ($apiExercises as $ex) {
                $choices[$ex['name'] . ' (' . $ex['bodyPart'] . ')'] = $ex['id'];
            }
            yield ChoiceField::new('apiId', 'Predefined Exercise')
                ->setChoices($choices)
                ->setLabel('API Exercise')
                ->setHelp('Select an exercise from the API or fill the form manually')
                ->setRequired(false)
                ->setFormTypeOption('placeholder', 'Select an exercise from the API');

        }

    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Exercise) {
            return;
        }

        // If API exercise is selected
        if ($entityInstance->getApiId() !== null) {
            try {
                $apiData = $this->exerciseApi->fetchExerciseDetails(
                    (string) $entityInstance->getApiId(),
                    $this->exerciseImageDirectory
                );

                $entityInstance->setName($apiData['name']);
                $entityInstance->setTarget($apiData['target']);
                $entityInstance->setInstructions(implode("\n", $apiData['instructions']));

                // Save only the filename for API images
                if ($apiData['gifUrl']) {
                    $pathParts = pathinfo($apiData['gifUrl']);
                    $entityInstance->setImageUrl($pathParts['basename']);
                } else {
                    $entityInstance->setImageUrl(null);
                }

            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to fetch exercise details from API. Using manual data instead.');
                error_log('Failed to fetch exercise details: ' . $e->getMessage());
            }
        }
        // Manual upload
        elseif ($entityInstance->getImageUrl() instanceof UploadedFile) {
            $file = $entityInstance->getImageUrl();
            $fileName = md5(uniqid()).'.'.$file->guessExtension();
            $file->move($this->exerciseImageDirectory, $fileName);
            $entityInstance->setImageUrl(self::UPLOAD_PATH . $fileName);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Exercise) {
            return;
        }

        $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);

        // If API exercise is selected and changed
        if ($entityInstance->getApiId() !== null &&
            ($originalData['apiId'] !== $entityInstance->getApiId() || $entityInstance->getImageUrl() instanceof UploadedFile)) {
            try {
                $apiData = $this->exerciseApi->fetchExerciseDetails(
                    (string) $entityInstance->getApiId(),
                    $this->exerciseImageDirectory
                );

                $entityInstance->setName($apiData['name']);
                $entityInstance->setTarget($apiData['target']);
                $entityInstance->setInstructions(implode("\n", $apiData['instructions']));

                // Save only the filename for API images on update
                if ($apiData['gifUrl']) {
                    $pathParts = pathinfo($apiData['gifUrl']);
                    $entityInstance->setImageUrl($pathParts['basename']);
                } else {
                    $entityInstance->setImageUrl(null);
                }

                // Handle manual image upload if a new file is provided
                if ($entityInstance->getImageUrl() instanceof UploadedFile) {
                    // Delete old image if it exists and is a local file
                    $this->deleteOldImageIfItExistsAndIsALocalFile($originalData['imageUrl'], $entityInstance);
                }

            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to fetch updated exercise details from API. Keeping existing data.');
                error_log('Failed to fetch exercise details: ' . $e->getMessage());
            }
        }
        // Manual upload (only if not using API)
        elseif ($entityInstance->getApiId() === null && $entityInstance->getImageUrl() instanceof UploadedFile) {
            // Delete old image if it exists and is a local file
            $this->deleteOldImageIfItExistsAndIsALocalFile($originalData['imageUrl'], $entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * @param $imageUrl
     * @param Exercise $entityInstance
     * @return void
     */
    public function deleteOldImageIfItExistsAndIsALocalFile($imageUrl, Exercise $entityInstance): void
    {
        if ($imageUrl && str_starts_with($imageUrl, self::UPLOAD_PATH)) {
            $oldFilePath = $this->exerciseImageDirectory . basename($imageUrl);
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }

        $file = $entityInstance->getImageUrl();
        $fileName = md5(uniqid()) . '.' . $file->guessExtension();
        $file->move($this->exerciseImageDirectory, $fileName);
        $entityInstance->setImageUrl(self::UPLOAD_PATH . $fileName);
    }
}