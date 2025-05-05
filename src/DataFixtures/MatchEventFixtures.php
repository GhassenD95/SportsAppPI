<?php

namespace App\DataFixtures;

use App\Entity\MatchEvent;
use App\Entity\Team;
use App\Entity\Tournament;
use App\Enum\Sport;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MatchEventFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $teamReferences = [];
        $tournamentReferences = [];

        // Load all team references
        $i = 0;
        while ($this->hasReference('team-' . $i, Team::class)) {
            $teamReferences[] = 'team-' . $i;
            $i++;
        }

        // Load all tournament references by checking all sports
        foreach (Sport::cases() as $sport) {
            $i = 0;
            while ($this->hasReference('tournament_' . $sport->name . '_' . $i, Tournament::class)) {
                $tournamentReferences[] = 'tournament_' . $sport->name . '_' . $i;
                $i++;
            }
        }

        // Debug info
        echo "Team References: " . count($teamReferences) . "\n";
        echo "Tournament References: " . count($tournamentReferences) . "\n";

        // Only create tournament matches if we have tournaments
        if (!empty($tournamentReferences)) {
            // Create 30+ matches with tournaments
            $tournamentMatchCount = min(30, count($tournamentReferences) * 4);
            for ($i = 0; $i < $tournamentMatchCount; $i++) {
                // Get random tournament
                $tournamentRef = $tournamentReferences[array_rand($tournamentReferences)];
                $tournament = $this->getReference($tournamentRef, Tournament::class);

                // Get teams from this tournament
                $teams = $tournament->getTeams()->toArray();

                if (count($teams) < 2) {
                    echo "Skipping tournament with insufficient teams\n";
                    continue; // Skip if tournament doesn't have enough teams
                }

                // Get two different random teams from the tournament
                shuffle($teams);
                $homeTeam = $teams[0];
                $awayTeam = $teams[1];

                // Ensure we don't have a team playing against itself
                $attempts = 0;
                while ($homeTeam === $awayTeam && $attempts < 5) {
                    shuffle($teams);
                    $awayTeam = $teams[1];
                    $attempts++;
                }

                if ($homeTeam === $awayTeam) {
                    echo "Skipping match with same home/away team\n";
                    continue; // Skip if couldn't find different teams
                }

                $match = $this->createMatchEvent(
                    $faker,
                    $homeTeam,
                    $awayTeam,
                    $tournament
                );

                $manager->persist($match);
            }
        }

        // Create 20+ friendly matches (no tournament)
        $friendlyMatchCount = 20;
        for ($i = 0; $i < $friendlyMatchCount; $i++) {
            // Skip if we don't have enough teams
            if (count($teamReferences) < 2) {
                echo "Insufficient teams for friendly match\n";
                continue;
            }

            // Get two different random teams
            shuffle($teamReferences);
            $homeTeamRef = $teamReferences[0];
            $awayTeamRef = $teamReferences[1];

            // Get team entities
            $homeTeam = $this->getReference($homeTeamRef, Team::class);
            $awayTeam = $this->getReference($awayTeamRef, Team::class);

            // Ensure different teams and same sport
            $attempts = 0;
            while (($homeTeam === $awayTeam || $homeTeam->getSport() !== $awayTeam->getSport()) && $attempts < 10) {
                shuffle($teamReferences);
                $awayTeamRef = $teamReferences[1];
                $awayTeam = $this->getReference($awayTeamRef, Team::class);
                $attempts++;
            }

            if ($homeTeam === $awayTeam || $homeTeam->getSport() !== $awayTeam->getSport()) {
                echo "Skipping friendly match with incompatible teams\n";
                continue; // Skip if couldn't find compatible teams
            }

            $match = $this->createMatchEvent(
                $faker,
                $homeTeam,
                $awayTeam,
                null // No tournament for friendly matches
            );

            $manager->persist($match);
        }

        $manager->flush();
    }

    private function createMatchEvent(
        $faker,
        Team $homeTeam,
        Team $awayTeam,
        ?Tournament $tournament
    ): MatchEvent {
        $match = new MatchEvent();

        // Set match date within tournament dates or random future date
        if ($tournament) {
            $match->setDate($faker->dateTimeBetween(
                $tournament->getStartDate(),
                $tournament->getEndDate()
            ));
        } else {
            $match->setDate($faker->dateTimeBetween('+1 week', '+3 months'));
        }

        $match->setLocation($faker->city() . ' ' . $faker->randomElement(['Stadium', 'Arena', 'Field', 'Sports Complex']));
        $match->setHomeTeam($homeTeam);

        // Set away team info (simulating external teams)
        $match->setAwayTeam([
            'name' => $this->generateAwayTeamName($faker, $homeTeam->getSport()),
            'club' => $faker->company(),
            'logo' => $this->getTeamLogo($faker->word())
        ]);

        // Set random score (60% chance of completed match with scores)
        if ($faker->boolean(60)) {
            $maxGoals = $homeTeam->getSport() === 'basketball' ? 120 : 5;
            $match->setScore([
                'home' => $faker->numberBetween(0, $maxGoals),
                'away' => $faker->numberBetween(0, $maxGoals)
            ]);
        } else {
            // Future match, no score yet
            $match->setScore(['home' => 0, 'away' => 0]);
        }

        if ($tournament) {
            $match->setTournament($tournament);
        }

        return $match;
    }

    private function generateAwayTeamName($faker, string $sport): string
    {
        $formats = [
            'football' => ['{city} FC', '{city} United', '{city} Rovers'],
            'basketball' => ['{city} Hoops', '{city} Ballers', '{city} Dunkers'],
            'volleyball' => ['{city} Spikers', '{city} Nets', '{city} Smashers']
        ];

        $template = $formats[$sport][array_rand($formats[$sport])];
        return str_replace('{city}', $faker->city(), $template);
    }

    private function getTeamLogo(string $name): string
    {
        $name = urlencode($name);
        return "https://ui-avatars.com/api/?name=$name&background=random&color=fff&size=128";
    }

    public function getDependencies(): array
    {
        return [
            TeamFixtures::class,
            TournamentFixtures::class
        ];
    }
}