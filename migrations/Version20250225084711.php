<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250225084711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE medecin (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reclamation ADD medecin_id INT DEFAULT NULL, DROP idmedecin');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE6064044F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id)');
        $this->addSql('CREATE INDEX IDX_CE6064044F31A84 ON reclamation (medecin_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE6064044F31A84');
        $this->addSql('DROP TABLE medecin');
        $this->addSql('DROP INDEX IDX_CE6064044F31A84 ON reclamation');
        $this->addSql('ALTER TABLE reclamation ADD idmedecin VARCHAR(255) DEFAULT NULL, DROP medecin_id');
    }
}
