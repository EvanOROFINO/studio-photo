<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260522190020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE newsletter_subscriber (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, active TINYINT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_401562C3E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE testimonial (id INT AUTO_INCREMENT NOT NULL, author_name VARCHAR(100) NOT NULL, author_role VARCHAR(100) DEFAULT NULL, content LONGTEXT NOT NULL, rating INT NOT NULL, published TINYINT NOT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE newsletter_subscriber');
        $this->addSql('DROP TABLE testimonial');
    }
}
