<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250916135221 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rpa_process_overview_process (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, rank INT NOT NULL, process_id INT NOT NULL, INDEX IDX_13CB2757EC2F574 (process_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE rpa_process_overview_process_overview (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, options LONGTEXT NOT NULL, group_id INT NOT NULL, INDEX IDX_437BDF18FE54D947 (group_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE rpa_process_overview_process_overview_group (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE rpa_process_overview_process ADD CONSTRAINT FK_13CB2757EC2F574 FOREIGN KEY (process_id) REFERENCES rpa_process_overview_process_overview (id)');
        $this->addSql('ALTER TABLE rpa_process_overview_process_overview ADD CONSTRAINT FK_437BDF18FE54D947 FOREIGN KEY (group_id) REFERENCES rpa_process_overview_process_overview_group (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rpa_process_overview_process DROP FOREIGN KEY FK_13CB2757EC2F574');
        $this->addSql('ALTER TABLE rpa_process_overview_process_overview DROP FOREIGN KEY FK_437BDF18FE54D947');
        $this->addSql('DROP TABLE rpa_process_overview_process');
        $this->addSql('DROP TABLE rpa_process_overview_process_overview');
        $this->addSql('DROP TABLE rpa_process_overview_process_overview_group');
    }
}
