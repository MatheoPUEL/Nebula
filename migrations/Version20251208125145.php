<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251208125145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, content LONGTEXT NOT NULL, target_type VARCHAR(50) NOT NULL, target_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9474526CF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE apod CHANGE url url VARCHAR(255) DEFAULT NULL, CHANGE hdurl hdurl VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE country CHANGE iso iso VARCHAR(2) NOT NULL, CHANGE iso3 iso3 VARCHAR(3) NOT NULL, CHANGE numcode numcode INT DEFAULT NULL, CHANGE phonecode phonecode INT NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE description description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user RENAME INDEX idx_user_time_zone TO IDX_8D93D649CBAB9ECD');

        $this->addSql('ALTER TABLE comment ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C727ACA70 FOREIGN KEY (parent_id) REFERENCES comment (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_9474526C727ACA70 ON comment (parent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF675F31B');
        $this->addSql('DROP TABLE comment');
        $this->addSql('ALTER TABLE apod CHANGE url url TEXT DEFAULT NULL, CHANGE hdurl hdurl TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE description description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE user RENAME INDEX idx_8d93d649cbab9ecd TO IDX_USER_TIME_ZONE');
        $this->addSql('ALTER TABLE country CHANGE iso iso CHAR(2) NOT NULL, CHANGE iso3 iso3 CHAR(3) DEFAULT NULL, CHANGE numcode numcode SMALLINT DEFAULT NULL, CHANGE phonecode phonecode SMALLINT NOT NULL');

        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C727ACA70');
        $this->addSql('DROP INDEX IDX_9474526C727ACA70 ON comment');
        $this->addSql('ALTER TABLE comment DROP parent_id');
    }
}
