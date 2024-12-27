<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211224090032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added timeout to command and scheduled_command tables';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE synolia_commands ADD timeout INT DEFAULT NULL');
        $this->addSql('ALTER TABLE synolia_scheduled_commands ADD timeout INT DEFAULT NULL');
        $this->addSql('ALTER TABLE synolia_commands ADD idleTimeout INT DEFAULT NULL');
        $this->addSql('ALTER TABLE synolia_scheduled_commands ADD idleTimeout INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE synolia_commands DROP idleTimeout');
        $this->addSql('ALTER TABLE synolia_scheduled_commands DROP idleTimeout');
        $this->addSql('ALTER TABLE synolia_commands DROP timeout');
        $this->addSql('ALTER TABLE synolia_scheduled_commands DROP timeout');
    }
}
