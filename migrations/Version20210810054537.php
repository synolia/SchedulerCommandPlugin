<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210810054537 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added new tables for history';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE synolia_commands (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) NOT NULL,
          command VARCHAR(255) NOT NULL,
          arguments VARCHAR(255) DEFAULT NULL,
          cronExpression VARCHAR(255) NOT NULL,
          logFilePrefix VARCHAR(255) DEFAULT NULL,
          priority INT NOT NULL,
          executeImmediately TINYINT(1) NOT NULL,
          enabled TINYINT(1) NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE synolia_scheduled_commands (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) NOT NULL,
          command VARCHAR(255) NOT NULL,          
          state VARCHAR(255) NOT NULL,
          arguments VARCHAR(255) DEFAULT NULL,
          executed_at DATETIME DEFAULT NULL,
          lastReturnCode INT DEFAULT NULL,
          logFile VARCHAR(255) DEFAULT NULL,
          commandEndTime DATETIME DEFAULT NULL,
          created_At DATETIME NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE scheduled_command');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE scheduled_command (
          id INT AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          command VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          arguments VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          cronExpression VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`,
          lastExecution DATETIME DEFAULT NULL,
          lastReturnCode INT DEFAULT NULL,
          logFile VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
          priority INT NOT NULL,
          executeImmediately TINYINT(1) NOT NULL,
          enabled TINYINT(1) NOT NULL,
          commandEndTime DATETIME DEFAULT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('DROP TABLE synolia_commands');
        $this->addSql('DROP TABLE synolia_scheduled_commands');
    }
}
