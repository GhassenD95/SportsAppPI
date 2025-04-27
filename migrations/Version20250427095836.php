<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250427095836 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE equipment DROP FOREIGN KEY FK_D338D583296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipment ADD CONSTRAINT FK_D338D583296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE SET NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE equipment DROP FOREIGN KEY FK_D338D583296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipment ADD CONSTRAINT FK_D338D583296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
    }
}
