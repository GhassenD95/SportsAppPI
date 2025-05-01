<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250501081430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE training (id INT AUTO_INCREMENT NOT NULL, facility_id INT NOT NULL, coach_id INT NOT NULL, team_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, start_time DATETIME NOT NULL, end_time DATETIME NOT NULL, INDEX IDX_D5128A8FA7014910 (facility_id), INDEX IDX_D5128A8F3C105691 (coach_id), INDEX IDX_D5128A8F296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training ADD CONSTRAINT FK_D5128A8FA7014910 FOREIGN KEY (facility_id) REFERENCES facility (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training ADD CONSTRAINT FK_D5128A8F3C105691 FOREIGN KEY (coach_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training ADD CONSTRAINT FK_D5128A8F296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE training DROP FOREIGN KEY FK_D5128A8FA7014910
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training DROP FOREIGN KEY FK_D5128A8F3C105691
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training DROP FOREIGN KEY FK_D5128A8F296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE training
        SQL);
    }
}
