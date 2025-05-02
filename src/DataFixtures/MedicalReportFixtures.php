<?php

namespace App\DataFixtures;

use App\Entity\MedicalReport;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Injuries;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MedicalReportFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    private const DIAGNOSIS_TYPES = [
        'Healthy - No Issues',
        'Minor Injury - Rest Required',
        'Moderate Injury - Treatment Needed',
        'Severe Injury - Extended Recovery',
        'Post-Surgery Recovery',
        'Rehabilitation Progress',
        'Cleared for Training',
        'Not Cleared for Play'
    ];

    private const STATUS_OPTIONS = [
        'Active',
        'Pending',
        'Completed',
        'Requires Follow-up',
        'Cleared',
        'Not Cleared'
    ];

    public static function getGroups(): array
    {
        return ['medical'];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Get all injuries from the database
        $injuries = $manager->getRepository(Injuries::class)->findAll();

        if (empty($injuries)) {
            throw new \RuntimeException('No injuries found. Make sure InjuriesFixtures is loaded first.');
        }

        // Create medical reports for each injury
        foreach ($injuries as $injury) {
            $report = new MedicalReport();
            
            // Set basic information
            $report->setPlayer($injury->getPlayer());
            $report->setTeam($injury->getTeam());
            $report->setReportDate($injury->getInjuryDate());
            $report->setDiagnosis($faker->randomElement(self::DIAGNOSIS_TYPES));
            $report->setStatus($faker->randomElement(self::STATUS_OPTIONS));
            
            // Set treatment and notes
            $report->setTreatment($injury->getTreatmentPlan());
            $report->setNotes($injury->getNotes() ?? $faker->optional(0.7)->text(200));
            
            // Set follow-up date (60% chance)
            if ($faker->boolean(60)) {
                $report->setFollowUpDate($faker->dateTimeBetween($injury->getInjuryDate(), '+3 months'));
            }

            // Link the report to the injury
            $report->setInjury($injury);
            
            $manager->persist($report);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            InjuriesFixtures::class,
        ];
    }
} 