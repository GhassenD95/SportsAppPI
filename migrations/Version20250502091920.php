<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250502091920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE medical_report ADD injury_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE medical_report ADD CONSTRAINT FK_AF71C02BABA45E9A FOREIGN KEY (injury_id) REFERENCES injuries (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_AF71C02BABA45E9A ON medical_report (injury_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE medical_report DROP FOREIGN KEY FK_AF71C02BABA45E9A
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_AF71C02BABA45E9A ON medical_report
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE medical_report DROP injury_id
        SQL);
    }
}
