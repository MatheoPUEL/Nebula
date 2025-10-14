<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251014090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table time_zone et ajout de la relation user.time_zone';
    }

    public function up(Schema $schema): void
    {
        // Création de la table time_zone
        $this->addSql('
            CREATE TABLE time_zone (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(100) NOT NULL,
                offset_utc VARCHAR(10) NOT NULL,
                offset_minutes INT NOT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        // Ajout de la colonne et de la relation dans user
        $this->addSql('ALTER TABLE user ADD time_zone_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_USER_TIME_ZONE FOREIGN KEY (time_zone_id) REFERENCES time_zone (id)');
        $this->addSql('CREATE INDEX IDX_USER_TIME_ZONE ON user (time_zone_id)');

        $this->addSql(file_get_contents(__DIR__.'/SQL/timezone.sql'));
    }

    public function down(Schema $schema): void
    {
        // Suppression de la relation et de la table
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_USER_TIME_ZONE');
        $this->addSql('DROP INDEX IDX_USER_TIME_ZONE ON user');
        $this->addSql('ALTER TABLE user DROP time_zone_id');
        $this->addSql('DROP TABLE time_zone');
    }
}
