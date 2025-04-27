<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250427095019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE equipment (id INT AUTO_INCREMENT NOT NULL, facility_id INT DEFAULT NULL, team_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, quantity INT NOT NULL, price DOUBLE PRECISION NOT NULL, image_url VARCHAR(255) DEFAULT NULL, INDEX IDX_D338D583A7014910 (facility_id), INDEX IDX_D338D583296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE exercise (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, target VARCHAR(255) NOT NULL, api_id INT DEFAULT NULL, instructions LONGTEXT DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE facility (id INT AUTO_INCREMENT NOT NULL, manager_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, image_url VARCHAR(255) DEFAULT NULL, sports JSON NOT NULL, INDEX IDX_105994B2783E3463 (manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE match_event (id INT AUTO_INCREMENT NOT NULL, home_team_id INT NOT NULL, tournament_id INT DEFAULT NULL, date DATETIME NOT NULL, location VARCHAR(255) NOT NULL, away_team JSON NOT NULL, score JSON NOT NULL, INDEX IDX_85C475069C4C13F6 (home_team_id), INDEX IDX_85C4750633D1A3E7 (tournament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE password_reset_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_6B7BA4B6A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, coach_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, logo_url VARCHAR(255) DEFAULT NULL, sport VARCHAR(255) NOT NULL, INDEX IDX_C4E0A61F3C105691 (coach_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tournament (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tournament_team (tournament_id INT NOT NULL, team_id INT NOT NULL, INDEX IDX_F36D142133D1A3E7 (tournament_id), INDEX IDX_F36D1421296CD8AE (team_id), PRIMARY KEY(tournament_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, team_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, image_url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipment ADD CONSTRAINT FK_D338D583A7014910 FOREIGN KEY (facility_id) REFERENCES facility (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipment ADD CONSTRAINT FK_D338D583296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE facility ADD CONSTRAINT FK_105994B2783E3463 FOREIGN KEY (manager_id) REFERENCES user (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE match_event ADD CONSTRAINT FK_85C475069C4C13F6 FOREIGN KEY (home_team_id) REFERENCES team (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE match_event ADD CONSTRAINT FK_85C4750633D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE password_reset_token ADD CONSTRAINT FK_6B7BA4B6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F3C105691 FOREIGN KEY (coach_id) REFERENCES user (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournament_team ADD CONSTRAINT FK_F36D142133D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournament_team ADD CONSTRAINT FK_F36D1421296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD CONSTRAINT FK_8D93D649296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE equipment DROP FOREIGN KEY FK_D338D583A7014910
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipment DROP FOREIGN KEY FK_D338D583296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE facility DROP FOREIGN KEY FK_105994B2783E3463
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE match_event DROP FOREIGN KEY FK_85C475069C4C13F6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE match_event DROP FOREIGN KEY FK_85C4750633D1A3E7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE password_reset_token DROP FOREIGN KEY FK_6B7BA4B6A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F3C105691
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournament_team DROP FOREIGN KEY FK_F36D142133D1A3E7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournament_team DROP FOREIGN KEY FK_F36D1421296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP FOREIGN KEY FK_8D93D649296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE equipment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE exercise
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE facility
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE match_event
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE password_reset_token
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE team
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tournament
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tournament_team
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
