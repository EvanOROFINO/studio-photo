<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260523092132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE booking (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(20) NOT NULL, client_name VARCHAR(100) NOT NULL, client_email VARCHAR(180) NOT NULL, client_phone VARCHAR(30) DEFAULT NULL, event_date DATETIME NOT NULL, location VARCHAR(200) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, amount_total NUMERIC(8, 2) NOT NULL, deposit_amount NUMERIC(8, 2) NOT NULL, status VARCHAR(20) NOT NULL, stripe_session_id VARCHAR(255) DEFAULT NULL, stripe_payment_intent_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, paid_at DATETIME DEFAULT NULL, service_id INT NOT NULL, UNIQUE INDEX UNIQ_E00CEDDEAEA34913 (reference), INDEX IDX_E00CEDDEED5CA9E6 (service_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEED5CA9E6');
        $this->addSql('DROP TABLE booking');
    }
}
