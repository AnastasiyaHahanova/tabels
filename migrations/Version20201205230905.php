<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201205230905 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cell (id INT AUTO_INCREMENT NOT NULL, spreadsheet INT NOT NULL, rowIndex INT NOT NULL, columnIndex INT NOT NULL, value NUMERIC(15, 6) NOT NULL, INDEX IDX_CB8787E243EA29E8 (spreadsheet), UNIQUE INDEX unique_row_column_idx (rowIndex, columnIndex), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `spreadsheet` (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, columns LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', deleted TINYINT(1) NOT NULL, INDEX IDX_43EA29E8A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, deleted TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_role (user INT NOT NULL, role INT NOT NULL, INDEX IDX_2DE8C6A38D93D649 (user), INDEX IDX_2DE8C6A357698A6A (role), PRIMARY KEY(user, role)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cell ADD CONSTRAINT FK_CB8787E243EA29E8 FOREIGN KEY (spreadsheet) REFERENCES `spreadsheet` (id)');
        $this->addSql('ALTER TABLE `spreadsheet` ADD CONSTRAINT FK_43EA29E8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A38D93D649 FOREIGN KEY (user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A357698A6A FOREIGN KEY (role) REFERENCES role (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A357698A6A');
        $this->addSql('ALTER TABLE cell DROP FOREIGN KEY FK_CB8787E243EA29E8');
        $this->addSql('ALTER TABLE `spreadsheet` DROP FOREIGN KEY FK_43EA29E8A76ED395');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A38D93D649');
        $this->addSql('DROP TABLE cell');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE `spreadsheet`');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_role');
    }
}
