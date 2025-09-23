<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250920194408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rpa_process_overview_process_overview ADD data_source_id INT NOT NULL');
        $this->addSql('ALTER TABLE rpa_process_overview_process_overview ADD CONSTRAINT FK_437BDF181A935C57 FOREIGN KEY (data_source_id) REFERENCES data_source (id)');
        $this->addSql('CREATE INDEX IDX_437BDF181A935C57 ON rpa_process_overview_process_overview (data_source_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rpa_process_overview_process_overview DROP FOREIGN KEY FK_437BDF181A935C57');
        $this->addSql('DROP INDEX IDX_437BDF181A935C57 ON rpa_process_overview_process_overview');
        $this->addSql('ALTER TABLE rpa_process_overview_process_overview DROP data_source_id');
    }
}
