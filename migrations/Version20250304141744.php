<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250304141744 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC77BA88B4D');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC72D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamation (id)');
        $this->addSql('CREATE INDEX IDX_5FB6DEC72D6BA2D9 ON reponse (reclamation_id)');
        $this->addSql('DROP INDEX fk_5fb6dec77ba88b4d ON reponse');
        $this->addSql('CREATE INDEX IDX_5FB6DEC77BA88B4D ON reponse (type_reclamation_id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC77BA88B4D FOREIGN KEY (type_reclamation_id) REFERENCES type_reclamation (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC72D6BA2D9');
        $this->addSql('DROP INDEX IDX_5FB6DEC72D6BA2D9 ON reponse');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC77BA88B4D');
        $this->addSql('DROP INDEX idx_5fb6dec77ba88b4d ON reponse');
        $this->addSql('CREATE INDEX FK_5FB6DEC77BA88B4D ON reponse (type_reclamation_id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC77BA88B4D FOREIGN KEY (type_reclamation_id) REFERENCES type_reclamation (id)');
    }
}
