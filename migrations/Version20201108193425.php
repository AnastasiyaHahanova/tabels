<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201108193425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE table_value (id INT AUTO_INCREMENT NOT NULL, table_id_id INT NOT NULL, row INT NOT NULL NOT NULL, `column` INT NOT NULL, value DOUBLE PRECISION NOT NULL, INDEX IDX_118B954C73B8532F (table_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE table_value ADD CONSTRAINT FK_118B954C73B8532F FOREIGN KEY (table_id_id) REFERENCES table_value (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE table_value DROP FOREIGN KEY FK_118B954C73B8532F');
        $this->addSql('DROP TABLE table_value');
    }
}
