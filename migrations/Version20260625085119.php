<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260625085119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE video_package (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, tagline VARCHAR(150) DEFAULT NULL, price INT NOT NULL, price_suffix VARCHAR(30) NOT NULL, features LONGTEXT DEFAULT NULL, delivery_time VARCHAR(100) DEFAULT NULL, featured TINYINT NOT NULL, is_active TINYINT NOT NULL, position INT NOT NULL, site_id INT DEFAULT NULL, INDEX IDX_3B858E38F6BD1646 (site_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE video_package ADD CONSTRAINT FK_3B858E38F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE video_package DROP FOREIGN KEY FK_3B858E38F6BD1646');
        $this->addSql('DROP TABLE video_package');
    }
}
