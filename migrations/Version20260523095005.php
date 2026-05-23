<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260523095005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(200) NOT NULL, slug VARCHAR(220) NOT NULL, excerpt LONGTEXT NOT NULL, content LONGTEXT NOT NULL, cover_image_name VARCHAR(255) DEFAULT NULL, meta_title VARCHAR(160) DEFAULT NULL, meta_description VARCHAR(200) DEFAULT NULL, published TINYINT NOT NULL, published_at DATETIME DEFAULT NULL, view_count INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, category_id INT NOT NULL, author_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_23A0E66989D9B62 (slug), INDEX IDX_23A0E6612469DE2 (category_id), INDEX IDX_23A0E66F675F31B (author_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE article_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(120) NOT NULL, description LONGTEXT DEFAULT NULL, position INT NOT NULL, UNIQUE INDEX UNIQ_53A4EDAA989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6612469DE2 FOREIGN KEY (category_id) REFERENCES article_category (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66F675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6612469DE2');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66F675F31B');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE article_category');
    }
}
