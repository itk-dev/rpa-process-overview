<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250926123652 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__data_source AS SELECT id, label, options, url FROM data_source');
        $this->addSql('DROP TABLE data_source');
        $this->addSql('CREATE TABLE data_source (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, label VARCHAR(255) NOT NULL, options CLOB DEFAULT NULL, url VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO data_source (id, label, options, url) SELECT id, label, options, url FROM __temp__data_source');
        $this->addSql('DROP TABLE __temp__data_source');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__data_source AS SELECT id, label, options, url FROM data_source');
        $this->addSql('DROP TABLE data_source');
        $this->addSql('CREATE TABLE data_source (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, label VARCHAR(255) NOT NULL, options CLOB NOT NULL, url VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO data_source (id, label, options, url) SELECT id, label, options, url FROM __temp__data_source');
        $this->addSql('DROP TABLE __temp__data_source');
    }
}
