<?php

namespace App\Controller\Admin;

use App\Entity\Exercise;
use App\Service\ExerciseApiService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ExerciseCrudController extends AbstractCrudController
{
    public function __construct(private ExerciseApiService $exerciseApi) {}

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
        $fields = [];

        // API Exercise Selection (only on forms)
        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            try {
                $apiExercises = $this->exerciseApi->getAllExerciseNames();
                $choices = [];
                foreach ($apiExercises as $ex) {
                    $choices[$ex['name'] . ' (' . $ex['bodyPart'] . ')'] = $ex['id'];
                }

                $fields[] = ChoiceField::new('apiId', 'Predefined Exercise')
                    ->setChoices($choices)
                    ->setRequired(false)
                    ->setHelp('Select to auto-fill all details from API')
                    ->onlyOnForms();

            } catch (\Exception $e) {
                $this->addFlash('warning', 'Could not load exercises from API. You can still create custom exercises.');
            }
        }

        // Exercise Details - simple display on detail page
        if ($pageName === Crud::PAGE_DETAIL) {
            $fields[] = TextField::new('name');
            $fields[] = TextField::new('target');
            $fields[] = TextareaField::new('instructions');
            $fields[] = ImageField::new('imageUrl')
                ->setBasePath('/uploads/exercises')
                ->onlyOnDetail();
            return $fields;
        }

        // Fields for index page
        if ($pageName === Crud::PAGE_INDEX) {
            $fields[] = IntegerField::new("id");
            $fields[] = TextField::new('name');
            $fields[] = TextField::new('target');
            $fields[] = TextareaField::new('instructions')
                ->setMaxLength(50) // Show only first 50 chars
                ->stripTags();
            $fields[] = ImageField::new('imageUrl')
                ->setBasePath('/uploads/exercises');
            return $fields;
        }

        // Full form fields for new/edit
        $fields[] = TextField::new('name', 'Exercise Name*')
            ->setRequired(true)
            ->setFormTypeOption('attr', ['required' => true]);

        $fields[] = TextField::new('target', 'Target Muscle*')
            ->setRequired(true)
            ->setFormTypeOption('attr', ['required' => true]);

        $fields[] = TextareaField::new('instructions')
            ->setRequired(false);

        $fields[] = ImageField::new('imageUrl')
            ->setBasePath('/uploads/exercises')
            ->hideOnForm();

        $fields[] = ImageField::new('uploadedImage', 'Upload Image')
            ->setUploadDir('public/uploads/exercises')
            ->setBasePath('/uploads/exercises')
            ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
            ->onlyOnForms()
            ->setFormTypeOption('mapped', false);

        return $fields;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function persistEntity(EntityManagerInterface $em, $exercise): void
    {
        if ($exercise->getApiId()) {
            try {
                $data = $this->exerciseApi->fetchExerciseDetails($exercise->getApiId());
                $exercise
                    ->setName($data['name'])
                    ->setTarget($data['target'])
                    ->setInstructions(implode("\n", $data['instructions'] ?? []))
                    ->setImageUrl($data['gifUrl'] ?? null);
            } catch (\Exception $e) {
                throw new \RuntimeException('Failed to load exercise details from API: '.$e->getMessage());
            }
        }

        parent::persistEntity($em, $exercise);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function updateEntity(EntityManagerInterface $em, $exercise): void
    {
        if ($exercise->getApiId()) {
            try {
                $data = $this->exerciseApi->fetchExerciseDetails($exercise->getApiId());
                $exercise
                    ->setName($data['name'])
                    ->setTarget($data['target'])
                    ->setInstructions(implode("\n", $data['instructions'] ?? []))
                    ->setImageUrl($data['gifUrl'] ?? null);
            } catch (\Exception $e) {
                throw new \RuntimeException('Failed to load exercise details from API: '.$e->getMessage());
            }
        }

        parent::updateEntity($em, $exercise);
    }
}