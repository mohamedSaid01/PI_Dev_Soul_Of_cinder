<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250304233218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reclamation DROP patient_id');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC71ADAD7EB');
        $this->addSql('DROP INDEX IDX_5FB6DEC71ADAD7EB ON reponse');
        $this->addSql('ALTER TABLE reponse ADD patient_id INT NOT NULL, DROP patient');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC76B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('CREATE INDEX IDX_5FB6DEC76B899279 ON reponse (patient_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reclamation ADD patient_id INT NOT NULL');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC76B899279');
        $this->addSql('DROP INDEX IDX_5FB6DEC76B899279 ON reponse');
        $this->addSql('ALTER TABLE reponse ADD patient INT DEFAULT NULL, DROP patient_id');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC71ADAD7EB FOREIGN KEY (patient) REFERENCES patient (id)');
        $this->addSql('CREATE INDEX IDX_5FB6DEC71ADAD7EB ON reponse (patient)');
    }
}
