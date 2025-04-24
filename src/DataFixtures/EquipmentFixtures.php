<?php

namespace App\DataFixtures;

use App\Entity\Equipment;
use App\Entity\Facility;
use App\Entity\Team;
use App\Enum\EquipmentState;
use App\Enum\EquipmentType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class EquipmentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Get all team references that exist
        $teamReferences = [];
        $i = 0;
        while ($this->hasReference('team_'.$i, Team::class)) {
            $teamReferences[] = $this->getReference('team_'.$i, Team::class);
            $i++;
        }

        // Initialize all equipment types with default configuration
        $equipmentTypes = [];
        foreach (EquipmentType::cases() as $type) {
            $equipmentTypes[$type->value] = [
                'names' => [ucfirst($type->value)],
                'placeholder' => 'https://via.placeholder.com/300.png/09f/fff?text='.ucfirst($type->value)
            ];
        }

        // Enhance specific types with more options
        $equipmentTypes[EquipmentType::BALL->value]['names'] = ['Basketball', 'Volleyball', 'Soccer Ball', 'Football'];
        $equipmentTypes[EquipmentType::NET->value]['names'] = ['Volleyball Net', 'Tennis Net', 'Badminton Net'];
        $equipmentTypes[EquipmentType::GOALPOST->value]['names'] = ['Soccer Goal', 'Hockey Goal'];
        $equipmentTypes[EquipmentType::UNIFORM->value]['names'] = ['Team Jersey', 'Practice Jersey', 'Shorts Set'];
        $equipmentTypes[EquipmentType::SHOES->value]['names'] = ['Basketball Shoes', 'Running Shoes', 'Cleats'];
        $equipmentTypes[EquipmentType::PROTECTIVE_GEAR->value]['names'] = ['Helmet', 'Shin Guards', 'Elbow Pads', 'Mouthguard'];
        $equipmentTypes[EquipmentType::TRAINING_EQUIPMENT->value]['names'] = ['Agility Ladder', 'Cones Set', 'Resistance Bands'];

        // Create 40 equipment items
        for ($i = 0; $i < 40; $i++) {
            $equipment = new Equipment();

            // Select random type
            $type = $faker->randomElement(EquipmentType::values());
            $typeConfig = $equipmentTypes[$type] ?? [
                'names' => [ucfirst($type)],
                'placeholder' => 'https://via.placeholder.com/300.png/09f/fff?text='.ucfirst($type)
            ];

            $equipment->setName($faker->randomElement($typeConfig['names']) . ' ' . $faker->numberBetween(1, 100));
            $equipment->setType($type);
            $equipment->setState($faker->randomElement(EquipmentState::values()));
            $equipment->setQuantity($faker->numberBetween(1, 20));
            $equipment->setPrice($faker->randomFloat(2, 10, 500));
            $equipment->setImageUrl($typeConfig['placeholder']);

            // Randomly assign to facility (70% chance)
            if ($faker->boolean(70)) {
                $facility = $this->getReference('facility_'.$faker->numberBetween(0, 29), Facility::class);
                $equipment->setFacility($facility);

                // Only assign to team if teams exist and 30% chance
                if (!empty($teamReferences) && $faker->boolean(30)) {
                    $team = $faker->randomElement($teamReferences);
                    $equipment->setTeam($team);
                }
            }

            $manager->persist($equipment);
            $this->addReference('equipment_'.$i, $equipment);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            FacilityFixtures::class,
            UserFixtures::class,
            TeamFixtures::class
        ];
    }
}