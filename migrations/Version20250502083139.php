<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250502083139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE player_performance (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, team_id INT NOT NULL, performance_date DATE NOT NULL, goals_scored INT NOT NULL, assists INT NOT NULL, minutes_played INT NOT NULL, shots_on_target INT NOT NULL, passes_completed INT NOT NULL, tackles INT NOT NULL, interceptions INT NOT NULL, saves INT NOT NULL, rating NUMERIC(3, 1) NOT NULL, notes LONGTEXT DEFAULT NULL, INDEX IDX_DAE07F1499E6F5DF (player_id), INDEX IDX_DAE07F14296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE team_performance (id INT AUTO_INCREMENT NOT NULL, team_id INT NOT NULL, performance_date DATE NOT NULL, goals_scored INT NOT NULL, goals_conceded INT NOT NULL, shots_on_target INT NOT NULL, shots_conceded INT NOT NULL, possession_percentage INT NOT NULL, passes_completed INT NOT NULL, tackles INT NOT NULL, interceptions INT NOT NULL, fouls INT NOT NULL, yellow_cards INT NOT NULL, red_cards INT NOT NULL, rating NUMERIC(3, 1) NOT NULL, notes LONGTEXT DEFAULT NULL, INDEX IDX_69E779CC296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_performance ADD CONSTRAINT FK_DAE07F1499E6F5DF FOREIGN KEY (player_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_performance ADD CONSTRAINT FK_DAE07F14296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_performance ADD CONSTRAINT FK_69E779CC296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE player_performance DROP FOREIGN KEY FK_DAE07F1499E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_performance DROP FOREIGN KEY FK_DAE07F14296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_performance DROP FOREIGN KEY FK_69E779CC296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE player_performance
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE team_performance
        SQL);
    }
}
