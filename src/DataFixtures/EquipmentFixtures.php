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
    private const SPORTS_IMAGE_API = 'https://api.pexels.com/v1/search?query=sports+';
    private const IMAGE_SIZE = 'medium'; // small, medium, large

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Get all team references
        $teamReferences = [];
        $i = 0;
        while ($this->hasReference('team_'.$i, Team::class)) {
            $teamReferences[] = $this->getReference('team_'.$i, Team::class);
            $i++;
        }

        // Enhanced equipment type configuration with better image URLs
        $equipmentTypes = [
            EquipmentType::BALL->value => [
                'names' => ['Basketball', 'Volleyball', 'Soccer Ball', 'Football'],
                'image' => 'https://images.pexels.com/photos/1839919/pexels-photo-1839919.jpeg'
            ],
            EquipmentType::NET->value => [
                'names' => ['Volleyball Net', 'Tennis Net', 'Badminton Net'],
                'image' => 'https://images.pexels.com/photos/209977/pexels-photo-209977.jpeg'
            ],
            EquipmentType::GOALPOST->value => [
                'names' => ['Soccer Goal', 'Hockey Goal'],
                'image' => 'https://images.pexels.com/photos/399187/pexels-photo-399187.jpeg'
            ],
            EquipmentType::UNIFORM->value => [
                'names' => ['Team Jersey', 'Practice Jersey', 'Shorts Set'],
                'image' => 'https://images.pexels.com/photos/942872/pexels-photo-942872.jpeg'
            ],
            EquipmentType::SHOES->value => [
                'names' => ['Basketball Shoes', 'Running Shoes', 'Cleats'],
                'image' => 'https://images.pexels.com/photos/2529148/pexels-photo-2529148.jpeg'
            ],
            EquipmentType::PROTECTIVE_GEAR->value => [
                'names' => ['Helmet', 'Shin Guards', 'Elbow Pads', 'Mouthguard'],
                'image' => 'https://images.pexels.com/photos/163452/basketball-dunk-blue-game-163452.jpeg'
            ],
            EquipmentType::TRAINING_EQUIPMENT->value => [
                'names' => ['Agility Ladder', 'Cones Set', 'Resistance Bands'],
                'image' => 'https://images.pexels.com/photos/221247/pexels-photo-221247.jpeg'
            ],
        ];

        // Create 40 equipment items
        for ($i = 0; $i < 40; $i++) {
            $equipment = new Equipment();

            // Select random type
            $type = $faker->randomElement(EquipmentType::values());
            $typeConfig = $equipmentTypes[$type] ?? [
                'names' => [ucfirst($type)],
                'image' => $this->getRandomSportsImage($type)
            ];

            $equipment->setName($faker->randomElement($typeConfig['names']) . ' ' . $faker->numberBetween(1, 100));
            $equipment->setType($type);
            $equipment->setState($faker->randomElement(EquipmentState::values()));
            $equipment->setQuantity($faker->numberBetween(1, 20));
            $equipment->setPrice($faker->randomFloat(2, 10, 500));
            $equipment->setImageUrl($typeConfig['image']);

            // Randomly assign to facility (70% chance)
            if ($faker->boolean(70)) {
                $facility = $this->getReference('facility-'.$faker->numberBetween(0, 29), Facility::class);
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

    private function getRandomSportsImage(string $type): string
    {
        // Fallback images if Pexels doesn't have a good match
        $fallbackImages = [
            'https://images.pexels.com/photos/863988/pexels-photo-863988.jpeg', // General sports
            'https://images.pexels.com/photos/34514/spot-runs-start-la.jpg',    // Running
            'https://images.pexels.com/photos/209841/pexels-photo-209841.jpeg', // Football
            'https://images.pexels.com/photos/248547/pexels-photo-248547.jpeg'  // Basketball
        ];

        return $fallbackImages[array_rand($fallbackImages)];
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