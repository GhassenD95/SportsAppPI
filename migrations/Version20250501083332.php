<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250501083332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE training_exercise (id INT AUTO_INCREMENT NOT NULL, training_id INT NOT NULL, exercise_id INT NOT NULL, duration_minutes INT NOT NULL, intensity VARCHAR(255) NOT NULL, notes LONGTEXT DEFAULT NULL, INDEX IDX_49BFC68BBEFD98D1 (training_id), INDEX IDX_49BFC68BE934951A (exercise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_exercise ADD CONSTRAINT FK_49BFC68BBEFD98D1 FOREIGN KEY (training_id) REFERENCES training (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_exercise ADD CONSTRAINT FK_49BFC68BE934951A FOREIGN KEY (exercise_id) REFERENCES exercise (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE training_exercise DROP FOREIGN KEY FK_49BFC68BBEFD98D1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE training_exercise DROP FOREIGN KEY FK_49BFC68BE934951A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE training_exercise
        SQL);
    }
}
