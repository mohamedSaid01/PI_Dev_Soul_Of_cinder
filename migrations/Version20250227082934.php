<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250227082934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE patient (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reponse ADD type_recalmation_id INT DEFAULT NULL, ADD patient VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_900BE75B45C24DFA FOREIGN KEY (type_recalmation_id) REFERENCES type_reclamation (id)');
        $this->addSql('CREATE INDEX IDX_900BE75B45C24DFA ON reponse (type_recalmation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE patient');
        $this->addSql('ALTER TABLE Reponse DROP FOREIGN KEY FK_900BE75B45C24DFA');
        $this->addSql('DROP INDEX IDX_900BE75B45C24DFA ON Reponse');
        $this->addSql('ALTER TABLE Reponse DROP type_recalmation_id, DROP patient');
    }
}
