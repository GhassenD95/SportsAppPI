<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250502075237 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE injuries (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, team_id INT NOT NULL, injury_type VARCHAR(50) NOT NULL, severity VARCHAR(20) NOT NULL, injury_date DATETIME NOT NULL, expected_recovery_date DATETIME NOT NULL, actual_recovery_date DATETIME DEFAULT NULL, description LONGTEXT NOT NULL, treatment_plan LONGTEXT NOT NULL, status VARCHAR(20) NOT NULL, notes LONGTEXT DEFAULT NULL, INDEX IDX_BEE4371E99E6F5DF (player_id), INDEX IDX_BEE4371E296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE injury (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE medical_report (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE injuries ADD CONSTRAINT FK_BEE4371E99E6F5DF FOREIGN KEY (player_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE injuries ADD CONSTRAINT FK_BEE4371E296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE injuries DROP FOREIGN KEY FK_BEE4371E99E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE injuries DROP FOREIGN KEY FK_BEE4371E296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE injuries
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE injury
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE medical_report
        SQL);
    }
}
