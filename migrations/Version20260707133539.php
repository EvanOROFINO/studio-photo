<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260707133539 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le champ "favorite" aux photos client (sélection de favoris par le client).';
    }

    public function up(Schema $schema): void
    {
        // DEFAULT 0 pour que les lignes existantes reçoivent une valeur en mode SQL strict.
        $this->addSql('ALTER TABLE client_photo ADD favorite TINYINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_photo DROP favorite');
    }
}
