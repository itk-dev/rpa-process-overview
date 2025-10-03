<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250926120922 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE data_source (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, label VARCHAR(255) NOT NULL, options CLOB NOT NULL)');
        $this->addSql('CREATE TABLE rpa_process_overview_process (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, rank INTEGER NOT NULL, process_id INTEGER NOT NULL, CONSTRAINT FK_13CB2757EC2F574 FOREIGN KEY (process_id) REFERENCES rpa_process_overview_process_overview (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_13CB2757EC2F574 ON rpa_process_overview_process (process_id)');
        $this->addSql('CREATE TABLE rpa_process_overview_process_overview (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, label VARCHAR(255) NOT NULL, options CLOB DEFAULT NULL, process_id VARCHAR(255) DEFAULT NULL, group_id INTEGER NOT NULL, data_source_id INTEGER NOT NULL, CONSTRAINT FK_437BDF18FE54D947 FOREIGN KEY (group_id) REFERENCES rpa_process_overview_process_overview_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_437BDF181A935C57 FOREIGN KEY (data_source_id) REFERENCES data_source (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_437BDF18FE54D947 ON rpa_process_overview_process_overview (group_id)');
        $this->addSql('CREATE INDEX IDX_437BDF181A935C57 ON rpa_process_overview_process_overview (data_source_id)');
        $this->addSql('CREATE TABLE rpa_process_overview_process_overview_group (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, label VARCHAR(255) NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE data_source');
        $this->addSql('DROP TABLE rpa_process_overview_process');
        $this->addSql('DROP TABLE rpa_process_overview_process_overview');
        $this->addSql('DROP TABLE rpa_process_overview_process_overview_group');
    }
}
