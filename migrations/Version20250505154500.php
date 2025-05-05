<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250505154500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Phone entity to User relationship';
    }

    public function up(Schema $schema): void
    {
        // Check if the phone table already exists
        $schemaManager = $this->connection->createSchemaManager();
        $existingTables = $schemaManager->listTableNames();

        if (!in_array('phone', $existingTables)) {
            // Create phone table only if it doesn't exist
            $this->addSql('CREATE TABLE phone (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                phone_number VARCHAR(20) NOT NULL, 
                type VARCHAR(20) NOT NULL, 
                verified TINYINT(1) NOT NULL, 
                verification_code VARCHAR(10) DEFAULT NULL, 
                INDEX IDX_444F97DDA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

            // Add foreign key constraint
            $this->addSql('ALTER TABLE phone 
                ADD CONSTRAINT FK_444F97DDA76ED395 
                FOREIGN KEY (user_id) REFERENCES user(id) 
                ON DELETE SET NULL
            ');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE phone DROP FOREIGN KEY FK_444F97DDA76ED395');
        $this->addSql('DROP TABLE phone');
    }
}
