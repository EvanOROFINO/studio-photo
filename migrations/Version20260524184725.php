<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260524184725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, slug VARCHAR(180) NOT NULL, description LONGTEXT NOT NULL, format VARCHAR(60) DEFAULT NULL, price NUMERIC(8, 2) NOT NULL, stock INT NOT NULL, image_name VARCHAR(255) DEFAULT NULL, featured TINYINT NOT NULL, published TINYINT NOT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_D34A04AD989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE shop_order (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(20) NOT NULL, customer_name VARCHAR(100) NOT NULL, customer_email VARCHAR(180) NOT NULL, customer_phone VARCHAR(30) DEFAULT NULL, shipping_address LONGTEXT NOT NULL, shipping_zip VARCHAR(20) NOT NULL, shipping_city VARCHAR(80) NOT NULL, shipping_country VARCHAR(80) NOT NULL, subtotal NUMERIC(8, 2) NOT NULL, shipping_fee NUMERIC(8, 2) NOT NULL, total_amount NUMERIC(8, 2) NOT NULL, status VARCHAR(20) NOT NULL, stripe_session_id VARCHAR(255) DEFAULT NULL, stripe_payment_intent_id VARCHAR(255) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, paid_at DATETIME DEFAULT NULL, shipped_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_323FC9CAAEA34913 (reference), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE shop_order_item (id INT AUTO_INCREMENT NOT NULL, product_title VARCHAR(200) NOT NULL, product_format VARCHAR(100) DEFAULT NULL, unit_price NUMERIC(8, 2) NOT NULL, quantity INT NOT NULL, order_id INT DEFAULT NULL, product_id INT DEFAULT NULL, INDEX IDX_2899F22F8D9F6D38 (order_id), INDEX IDX_2899F22F4584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE shop_order_item ADD CONSTRAINT FK_2899F22F8D9F6D38 FOREIGN KEY (order_id) REFERENCES shop_order (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shop_order_item ADD CONSTRAINT FK_2899F22F4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shop_order_item DROP FOREIGN KEY FK_2899F22F8D9F6D38');
        $this->addSql('ALTER TABLE shop_order_item DROP FOREIGN KEY FK_2899F22F4584665A');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE shop_order');
        $this->addSql('DROP TABLE shop_order_item');
    }
}
