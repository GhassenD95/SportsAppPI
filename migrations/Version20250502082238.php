<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250502082238 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE medical_report ADD player_id INT NOT NULL, ADD team_id INT NOT NULL, ADD report_date DATE NOT NULL, ADD diagnosis VARCHAR(255) NOT NULL, ADD treatment LONGTEXT NOT NULL, ADD notes LONGTEXT DEFAULT NULL, ADD follow_up_date DATE DEFAULT NULL, ADD status VARCHAR(50) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE medical_report ADD CONSTRAINT FK_AF71C02B99E6F5DF FOREIGN KEY (player_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE medical_report ADD CONSTRAINT FK_AF71C02B296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AF71C02B99E6F5DF ON medical_report (player_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AF71C02B296CD8AE ON medical_report (team_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE medical_report DROP FOREIGN KEY FK_AF71C02B99E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE medical_report DROP FOREIGN KEY FK_AF71C02B296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_AF71C02B99E6F5DF ON medical_report
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_AF71C02B296CD8AE ON medical_report
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE medical_report DROP player_id, DROP team_id, DROP report_date, DROP diagnosis, DROP treatment, DROP notes, DROP follow_up_date, DROP status
        SQL);
    }
}
