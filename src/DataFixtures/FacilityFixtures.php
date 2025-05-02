<?php

namespace App\DataFixtures;

use App\Entity\Facility;
use App\Entity\User;
use App\Enum\Sport;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FacilityFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Get all manager references dynamically
        $managers = [];
        for ($i = 0; $i < 3; $i++) {
            try {
                $managers[] = $this->getReference('manager-' . $i, User::class);
            } catch (\Exception $e) {
                break;
            }
        }

        if (empty($managers)) {
            throw new \RuntimeException('No manager users found. Make sure UserFixtures creates managers with references manager-0, manager-1, etc.');
        }

        // Sport configuration using your existing image files
        $sportConfig = [
            Sport::BASKETBALL->value => [
                'names' => ['%s Basketball Arena', '%s Hoops Center', '%s Court'],
                'image' => 'defaults/basketball.png'
            ],
            Sport::VOLLEYBALL->value => [
                'names' => ['%s Volleyball Center', '%s Spike Arena', '%s Net Club'],
                'image' => 'defaults/volleyball.jpg'
            ],
            Sport::FOOTBALL->value => [
                'names' => ['%s Football Field', '%s Soccer Complex', '%s Pitch Arena'],
                'image' => 'defaults/football.png'
            ]
        ];

        // Create 30 facilities
        for ($i = 0; $i < 30; $i++) {
            $facility = new Facility();
            $city = $faker->city();

            // 60% single-sport, 40% multi-sport facilities
            if ($i < 18) {
                // Single-sport facility
                $sport = $faker->randomElement(Sport::values());
                $config = $sportConfig[$sport];
                $facility->setSports([$sport]);
                $facility->setName(sprintf($faker->randomElement($config['names']), $city));
                $facility->setImageUrl($config['image']);
            } else {
                // Multi-sport facility
                $sports = $faker->randomElements(Sport::values(), $faker->numberBetween(2, count(Sport::values())));
                $facility->setSports($sports);
                $facility->setName(sprintf('%s Sports Complex', $city));
                $facility->setImageUrl('defaults/'.($faker->boolean() ? 'basketball.png' : 'volleyball.jpg'));
            }

            $facility->setLocation(sprintf(
                '%s, %s %s',
                $faker->streetAddress(),
                $city,
                $faker->postcode()
            ));

            // Assign random manager
            $facility->setManager($faker->randomElement($managers));

            $manager->persist($facility);
            $this->addReference('facility-'.$i, $facility);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class
        ];
    }
}