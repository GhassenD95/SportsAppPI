<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250424002325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE equipement (id INT AUTO_INCREMENT NOT NULL, facility_id INT DEFAULT NULL, team_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, quantity INT NOT NULL, price DOUBLE PRECISION NOT NULL, image_url VARCHAR(255) DEFAULT NULL, INDEX IDX_B8B4C6F3A7014910 (facility_id), INDEX IDX_B8B4C6F3296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipement ADD CONSTRAINT FK_B8B4C6F3A7014910 FOREIGN KEY (facility_id) REFERENCES facility (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipement ADD CONSTRAINT FK_B8B4C6F3296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE equipement DROP FOREIGN KEY FK_B8B4C6F3A7014910
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipement DROP FOREIGN KEY FK_B8B4C6F3296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE equipement
        SQL);
    }
}
