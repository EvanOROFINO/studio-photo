<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260625082205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article ADD site_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('CREATE INDEX IDX_23A0E66F6BD1646 ON article (site_id)');
        $this->addSql('ALTER TABLE before_after ADD site_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE before_after ADD CONSTRAINT FK_4459C211F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('CREATE INDEX IDX_4459C211F6BD1646 ON before_after (site_id)');
        $this->addSql('ALTER TABLE photo ADD site_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B78418F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('CREATE INDEX IDX_14B78418F6BD1646 ON photo (site_id)');
        $this->addSql('ALTER TABLE product ADD site_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('CREATE INDEX IDX_D34A04ADF6BD1646 ON product (site_id)');
        $this->addSql('ALTER TABLE service ADD site_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('CREATE INDEX IDX_E19D9AD2F6BD1646 ON service (site_id)');
        $this->addSql('ALTER TABLE testimonial ADD site_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE testimonial ADD CONSTRAINT FK_E6BDCDF7F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('CREATE INDEX IDX_E6BDCDF7F6BD1646 ON testimonial (site_id)');
        $this->addSql('ALTER TABLE video ADD site_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE video ADD CONSTRAINT FK_7CC7DA2CF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('CREATE INDEX IDX_7CC7DA2CF6BD1646 ON video (site_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66F6BD1646');
        $this->addSql('DROP INDEX IDX_23A0E66F6BD1646 ON article');
        $this->addSql('ALTER TABLE article DROP site_id');
        $this->addSql('ALTER TABLE before_after DROP FOREIGN KEY FK_4459C211F6BD1646');
        $this->addSql('DROP INDEX IDX_4459C211F6BD1646 ON before_after');
        $this->addSql('ALTER TABLE before_after DROP site_id');
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B78418F6BD1646');
        $this->addSql('DROP INDEX IDX_14B78418F6BD1646 ON photo');
        $this->addSql('ALTER TABLE photo DROP site_id');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADF6BD1646');
        $this->addSql('DROP INDEX IDX_D34A04ADF6BD1646 ON product');
        $this->addSql('ALTER TABLE product DROP site_id');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD2F6BD1646');
        $this->addSql('DROP INDEX IDX_E19D9AD2F6BD1646 ON service');
        $this->addSql('ALTER TABLE service DROP site_id');
        $this->addSql('ALTER TABLE testimonial DROP FOREIGN KEY FK_E6BDCDF7F6BD1646');
        $this->addSql('DROP INDEX IDX_E6BDCDF7F6BD1646 ON testimonial');
        $this->addSql('ALTER TABLE testimonial DROP site_id');
        $this->addSql('ALTER TABLE video DROP FOREIGN KEY FK_7CC7DA2CF6BD1646');
        $this->addSql('DROP INDEX IDX_7CC7DA2CF6BD1646 ON video');
        $this->addSql('ALTER TABLE video DROP site_id');
    }
}
