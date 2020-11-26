<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201121204552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tables DROP FOREIGN KEY FK_84470221A76ED395');
        $this->addSql('ALTER TABLE tables ADD CONSTRAINT FK_84470221A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD token VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `tables` DROP FOREIGN KEY FK_84470221A76ED395');
        $this->addSql('ALTER TABLE `tables` ADD CONSTRAINT FK_84470221A76ED395 FOREIGN KEY (user_id) REFERENCES tables (id)');
        $this->addSql('ALTER TABLE user DROP token');
    }
}
