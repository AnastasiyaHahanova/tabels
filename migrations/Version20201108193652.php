<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201108193652 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE table_value DROP FOREIGN KEY FK_118B954C73B8532F');
        $this->addSql('DROP INDEX IDX_118B954C73B8532F ON table_value');
        $this->addSql('ALTER TABLE table_value CHANGE table_id_id table_id INT NOT NULL');
        $this->addSql('ALTER TABLE table_value ADD CONSTRAINT FK_118B954CECFF285C FOREIGN KEY (table_id) REFERENCES table_value (id)');
        $this->addSql('CREATE INDEX IDX_118B954CECFF285C ON table_value (table_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE table_value DROP FOREIGN KEY FK_118B954CECFF285C');
        $this->addSql('DROP INDEX IDX_118B954CECFF285C ON table_value');
        $this->addSql('ALTER TABLE table_value CHANGE table_id table_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE table_value ADD CONSTRAINT FK_118B954C73B8532F FOREIGN KEY (table_id_id) REFERENCES table_value (id)');
        $this->addSql('CREATE INDEX IDX_118B954C73B8532F ON table_value (table_id_id)');
    }
}
