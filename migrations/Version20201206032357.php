<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201206032357 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX unique_row_column_idx ON cell');
        $this->addSql('CREATE UNIQUE INDEX unique_row_column_idx ON cell (rowIndex, columnIndex, spreadsheet)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX unique_row_column_idx ON cell');
        $this->addSql('CREATE UNIQUE INDEX unique_row_column_idx ON cell (rowIndex, columnIndex)');
    }
}
