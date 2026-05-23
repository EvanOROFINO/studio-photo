<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260523065630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client_gallery (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(64) NOT NULL, title VARCHAR(150) NOT NULL, client_name VARCHAR(100) NOT NULL, client_email VARCHAR(180) DEFAULT NULL, password_hash VARCHAR(255) NOT NULL, welcome_message LONGTEXT DEFAULT NULL, allow_download TINYINT NOT NULL, active TINYINT NOT NULL, shoot_date DATETIME DEFAULT NULL, expires_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, last_viewed_at DATETIME DEFAULT NULL, view_count INT NOT NULL, UNIQUE INDEX UNIQ_15163C475F37A13B (token), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE client_photo (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, original_name VARCHAR(255) DEFAULT NULL, image_size INT DEFAULT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, gallery_id INT NOT NULL, INDEX IDX_B7DA18AF4E7AF8F (gallery_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE client_photo ADD CONSTRAINT FK_B7DA18AF4E7AF8F FOREIGN KEY (gallery_id) REFERENCES client_gallery (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_photo DROP FOREIGN KEY FK_B7DA18AF4E7AF8F');
        $this->addSql('DROP TABLE client_gallery');
        $this->addSql('DROP TABLE client_photo');
    }
}
