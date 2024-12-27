<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210828114655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added owner of scheduled commands';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE synolia_scheduled_commands ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          synolia_scheduled_commands
        ADD
          CONSTRAINT FK_813781F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES synolia_commands (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_813781F7E3C61F9 ON synolia_scheduled_commands (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE synolia_scheduled_commands DROP FOREIGN KEY FK_813781F7E3C61F9');
        $this->addSql('DROP INDEX IDX_813781F7E3C61F9 ON synolia_scheduled_commands');
        $this->addSql('ALTER TABLE synolia_scheduled_commands DROP owner_id');
    }
}
