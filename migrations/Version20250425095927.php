<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250425095927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE match_event (id INT AUTO_INCREMENT NOT NULL, home_team_id INT NOT NULL, tournament_id INT DEFAULT NULL, date DATETIME NOT NULL, location VARCHAR(255) NOT NULL, away_team JSON NOT NULL, score JSON NOT NULL, INDEX IDX_85C475069C4C13F6 (home_team_id), INDEX IDX_85C4750633D1A3E7 (tournament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE match_event ADD CONSTRAINT FK_85C475069C4C13F6 FOREIGN KEY (home_team_id) REFERENCES team (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE match_event ADD CONSTRAINT FK_85C4750633D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE match_event DROP FOREIGN KEY FK_85C475069C4C13F6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE match_event DROP FOREIGN KEY FK_85C4750633D1A3E7
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE match_event
        SQL);
    }
}
