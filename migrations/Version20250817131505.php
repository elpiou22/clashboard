<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250817131505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE password_reset_request DROP FOREIGN KEY FK_C5D0A95AA76ED395');
        $this->addSql('ALTER TABLE password_reset_request ADD selector VARCHAR(100) NOT NULL, ADD hashed_token VARCHAR(100) NOT NULL, ADD requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP token, DROP created_at');
        $this->addSql('ALTER TABLE password_reset_request ADD CONSTRAINT FK_C5D0A95AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE password_reset_request DROP FOREIGN KEY FK_C5D0A95AA76ED395');
        $this->addSql('ALTER TABLE password_reset_request ADD token VARCHAR(255) NOT NULL, ADD created_at DATETIME NOT NULL, DROP selector, DROP hashed_token, DROP requested_at, DROP expires_at, DROP used_at');
        $this->addSql('ALTER TABLE password_reset_request ADD CONSTRAINT FK_C5D0A95AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }
}
