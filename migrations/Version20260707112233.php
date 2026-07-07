<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260707112233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client_film (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, url VARCHAR(255) NOT NULL, source VARCHAR(20) NOT NULL, external_id VARCHAR(100) DEFAULT NULL, duration VARCHAR(50) DEFAULT NULL, download_url VARCHAR(500) DEFAULT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, gallery_id INT NOT NULL, INDEX IDX_53D87D404E7AF8F (gallery_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE client_film ADD CONSTRAINT FK_53D87D404E7AF8F FOREIGN KEY (gallery_id) REFERENCES client_gallery (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_film DROP FOREIGN KEY FK_53D87D404E7AF8F');
        $this->addSql('DROP TABLE client_film');
    }
}
