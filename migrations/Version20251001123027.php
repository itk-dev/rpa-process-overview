<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251001123027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE data_source ADD COLUMN created_by VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE data_source ADD COLUMN updated_by VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE data_source ADD COLUMN created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE data_source ADD COLUMN updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE rpa_process_overview_process_overview ADD COLUMN created_by VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE rpa_process_overview_process_overview ADD COLUMN updated_by VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE rpa_process_overview_process_overview ADD COLUMN created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE rpa_process_overview_process_overview ADD COLUMN updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE rpa_process_overview_process_overview_group ADD COLUMN created_by VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE rpa_process_overview_process_overview_group ADD COLUMN updated_by VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE rpa_process_overview_process_overview_group ADD COLUMN created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE rpa_process_overview_process_overview_group ADD COLUMN updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__data_source AS SELECT id, label, options, url FROM data_source');
        $this->addSql('DROP TABLE data_source');
        $this->addSql('CREATE TABLE data_source (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, label VARCHAR(255) NOT NULL, options CLOB DEFAULT NULL, url VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO data_source (id, label, options, url) SELECT id, label, options, url FROM __temp__data_source');
        $this->addSql('DROP TABLE __temp__data_source');
        $this->addSql('CREATE TEMPORARY TABLE __temp__rpa_process_overview_process_overview AS SELECT id, label, options, process_id, group_id, data_source_id FROM rpa_process_overview_process_overview');
        $this->addSql('DROP TABLE rpa_process_overview_process_overview');
        $this->addSql('CREATE TABLE rpa_process_overview_process_overview (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, label VARCHAR(255) NOT NULL, options CLOB DEFAULT NULL, process_id VARCHAR(255) DEFAULT NULL, group_id INTEGER NOT NULL, data_source_id INTEGER NOT NULL, CONSTRAINT FK_437BDF18FE54D947 FOREIGN KEY (group_id) REFERENCES rpa_process_overview_process_overview_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_437BDF181A935C57 FOREIGN KEY (data_source_id) REFERENCES data_source (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO rpa_process_overview_process_overview (id, label, options, process_id, group_id, data_source_id) SELECT id, label, options, process_id, group_id, data_source_id FROM __temp__rpa_process_overview_process_overview');
        $this->addSql('DROP TABLE __temp__rpa_process_overview_process_overview');
        $this->addSql('CREATE INDEX IDX_437BDF18FE54D947 ON rpa_process_overview_process_overview (group_id)');
        $this->addSql('CREATE INDEX IDX_437BDF181A935C57 ON rpa_process_overview_process_overview (data_source_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__rpa_process_overview_process_overview_group AS SELECT id, label FROM rpa_process_overview_process_overview_group');
        $this->addSql('DROP TABLE rpa_process_overview_process_overview_group');
        $this->addSql('CREATE TABLE rpa_process_overview_process_overview_group (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, label VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO rpa_process_overview_process_overview_group (id, label) SELECT id, label FROM __temp__rpa_process_overview_process_overview_group');
        $this->addSql('DROP TABLE __temp__rpa_process_overview_process_overview_group');
    }
}
