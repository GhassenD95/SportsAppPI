<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Load UserFixtures first since other fixtures depend on it
        $userFixtures = new UserFixtures($this->passwordHasher);
        $userFixtures->setReferenceRepository($this->referenceRepository);
        $userFixtures->load($manager);

        // Load TeamFixtures next
        $teamFixtures = new TeamFixtures();
        $teamFixtures->setReferenceRepository($this->referenceRepository);
        $teamFixtures->load($manager);

        // Load InjuriesFixtures before MedicalReportFixtures
        $injuriesFixtures = new InjuriesFixtures();
        $injuriesFixtures->setReferenceRepository($this->referenceRepository);
        $injuriesFixtures->load($manager);

        // Load MedicalReportFixtures after InjuriesFixtures
        $medicalReportFixtures = new MedicalReportFixtures();
        $medicalReportFixtures->setReferenceRepository($this->referenceRepository);
        $medicalReportFixtures->load($manager);

        // Load performance fixtures last
        $playerPerformanceFixtures = new PlayerPerformanceFixtures();
        $playerPerformanceFixtures->setReferenceRepository($this->referenceRepository);
        $playerPerformanceFixtures->load($manager);

        $teamPerformanceFixtures = new TeamPerformanceFixtures();
        $teamPerformanceFixtures->setReferenceRepository($this->referenceRepository);
        $teamPerformanceFixtures->load($manager);

        $manager->flush();
    }
}
