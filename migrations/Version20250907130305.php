<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250907130305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attack CHANGE attacker_th attacker_th INT DEFAULT NULL, CHANGE defender_th defender_th INT DEFAULT NULL, CHANGE percentage percentage INT DEFAULT NULL, CHANGE attack_stars attack_stars INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attack CHANGE attacker_th attacker_th INT NOT NULL, CHANGE defender_th defender_th INT NOT NULL, CHANGE percentage percentage INT NOT NULL, CHANGE attack_stars attack_stars INT NOT NULL');
    }
}
