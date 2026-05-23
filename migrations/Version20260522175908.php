<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260522175908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(120) NOT NULL, description LONGTEXT DEFAULT NULL, position INT NOT NULL, UNIQUE INDEX UNIQ_64C19C1989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE contact_request (id INT AUTO_INCREMENT NOT NULL, full_name VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(30) DEFAULT NULL, project_type VARCHAR(30) NOT NULL, event_date DATETIME DEFAULT NULL, message LONGTEXT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE photo (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, image_size INT DEFAULT NULL, featured TINYINT NOT NULL, taken_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, category_id INT NOT NULL, INDEX IDX_14B7841812469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(120) NOT NULL, description LONGTEXT NOT NULL, price_from NUMERIC(8, 2) DEFAULT NULL, duration VARCHAR(60) DEFAULT NULL, icon VARCHAR(50) DEFAULT NULL, position INT NOT NULL, active TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, full_name VARCHAR(100) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B7841812469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B7841812469DE2');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE contact_request');
        $this->addSql('DROP TABLE photo');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
