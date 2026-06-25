<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260625081700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE site (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(20) NOT NULL, name VARCHAR(100) NOT NULL, domain VARCHAR(255) NOT NULL, domain_staging VARCHAR(255) DEFAULT NULL, tagline LONGTEXT DEFAULT NULL, primary_color VARCHAR(7) NOT NULL, accent_color VARCHAR(7) NOT NULL, icon_emoji VARCHAR(50) NOT NULL, is_active TINYINT NOT NULL, is_default TINYINT NOT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_694309E4989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE site');
    }
}
