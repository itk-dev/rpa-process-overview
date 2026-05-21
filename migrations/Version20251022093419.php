<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251022093419 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE data_source (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, options LONGTEXT DEFAULT NULL, url VARCHAR(255) NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE process_overview (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, options LONGTEXT DEFAULT NULL, process_id VARCHAR(255) DEFAULT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, group_id INT NOT NULL, data_source_id INT NOT NULL, INDEX IDX_11328F3AFE54D947 (group_id), INDEX IDX_11328F3A1A935C57 (data_source_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE process_overview_group (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE process_overview ADD CONSTRAINT FK_11328F3AFE54D947 FOREIGN KEY (group_id) REFERENCES process_overview_group (id)');
        $this->addSql('ALTER TABLE process_overview ADD CONSTRAINT FK_11328F3A1A935C57 FOREIGN KEY (data_source_id) REFERENCES data_source (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE process_overview DROP FOREIGN KEY FK_11328F3AFE54D947');
        $this->addSql('ALTER TABLE process_overview DROP FOREIGN KEY FK_11328F3A1A935C57');
        $this->addSql('DROP TABLE data_source');
        $this->addSql('DROP TABLE process_overview');
        $this->addSql('DROP TABLE process_overview_group');
        $this->addSql('DROP TABLE user');
    }
}
