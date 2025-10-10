<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251010083632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE country (
    id INT AUTO_INCREMENT NOT NULL,
    iso CHAR(2) NOT NULL,
    name VARCHAR(50) NOT NULL,
    nicename VARCHAR(50) NOT NULL,
    iso3 CHAR(3) NULL,
    numcode SMALLINT DEFAULT NULL,
    phonecode SMALLINT NOT NULL,
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4
  COLLATE `utf8mb4_unicode_ci`
  ENGINE = InnoDB;');
        $this->addSql('ALTER TABLE user ADD country_id INT DEFAULT NULL, CHANGE avatar avatar VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649F92F3E70 ON user (country_id)');

        $this->addSql(file_get_contents(__DIR__.'/SQL/countries.sql'));    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649F92F3E70');
        $this->addSql('DROP TABLE country');
        $this->addSql('DROP INDEX IDX_8D93D649F92F3E70 ON user');
        $this->addSql('ALTER TABLE user DROP country_id, CHANGE avatar avatar VARCHAR(50) DEFAULT NULL');
    }
}
