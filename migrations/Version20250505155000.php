<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250505155000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add injuries collection to User and Team entities';
    }

    public function up(Schema $schema): void
    {
        // Check if the team_id column already exists
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('injuries');
        $teamIdExists = false;

        foreach ($columns as $column) {
            if ($column->getName() === 'team_id') {
                $teamIdExists = true;
                break;
            }
        }

        // Add team_id column only if it doesn't exist
        if (!$teamIdExists) {
            $this->addSql('ALTER TABLE injuries ADD team_id INT DEFAULT NULL');
            $this->addSql('ALTER TABLE injuries 
                ADD CONSTRAINT FK_TEAM_INJURIES 
                FOREIGN KEY (team_id) REFERENCES team(id) 
                ON DELETE SET NULL
            ');
            $this->addSql('CREATE INDEX IDX_TEAM_INJURIES ON injuries (team_id)');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE injuries DROP FOREIGN KEY FK_TEAM_INJURIES');
        $this->addSql('DROP INDEX IDX_TEAM_INJURIES ON injuries');
        $this->addSql('ALTER TABLE injuries DROP team_id');
    }
}
