<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240101000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial migration for BricoMalin';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, `key` VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_64C19C18A90ABA9 (`key`), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password_hash VARCHAR(255) NOT NULL, display_name VARCHAR(255) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE professional_profile (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, siret VARCHAR(14) DEFAULT NULL, id_document_path VARCHAR(255) DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_8A2A2A4A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE job_request (id INT AUTO_INCREMENT NOT NULL, requester_id INT NOT NULL, category_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, department VARCHAR(3) NOT NULL, city VARCHAR(100) DEFAULT NULL, is_free TINYINT(1) NOT NULL, suggested_price NUMERIC(10, 2) DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8F2A2A4ED442CF4F (requester_id), INDEX IDX_8F2A2A4E12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE offer (id INT AUTO_INCREMENT NOT NULL, job_request_id INT NOT NULL, proposer_id INT NOT NULL, amount NUMERIC(10, 2) DEFAULT NULL, message LONGTEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_29D6873E1DA3D7D4 (job_request_id), INDEX IDX_29D6873E1A4B8B4E (proposer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, job_request_id INT NOT NULL, offer_id INT NOT NULL, payer_id INT NOT NULL, payee_id INT NOT NULL, mode VARCHAR(10) NOT NULL, status VARCHAR(20) NOT NULL, stripe_payment_intent_id VARCHAR(255) DEFAULT NULL, amount NUMERIC(10, 2) NOT NULL, currency VARCHAR(3) NOT NULL, after_work_code_requester VARCHAR(10) DEFAULT NULL, after_work_code_proposer VARCHAR(10) DEFAULT NULL, requester_validated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', proposer_validated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6D28840D1DA3D7D4 (job_request_id), INDEX IDX_6D28840D53C674EE (offer_id), INDEX IDX_6D28840D4B3B0C4C (payer_id), INDEX IDX_6D28840D5A2AA3A7 (payee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE professional_profile ADD CONSTRAINT FK_8A2A2A4A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE job_request ADD CONSTRAINT FK_8F2A2A4ED442CF4F FOREIGN KEY (requester_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE job_request ADD CONSTRAINT FK_8F2A2A4E12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873E1DA3D7D4 FOREIGN KEY (job_request_id) REFERENCES job_request (id)');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873E1A4B8B4E FOREIGN KEY (proposer_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D1DA3D7D4 FOREIGN KEY (job_request_id) REFERENCES job_request (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D53C674EE FOREIGN KEY (offer_id) REFERENCES offer (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D4B3B0C4C FOREIGN KEY (payer_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D5A2AA3A7 FOREIGN KEY (payee_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE professional_profile DROP FOREIGN KEY FK_8A2A2A4A76ED395');
        $this->addSql('ALTER TABLE job_request DROP FOREIGN KEY FK_8F2A2A4ED442CF4F');
        $this->addSql('ALTER TABLE job_request DROP FOREIGN KEY FK_8F2A2A4E12469DE2');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873E1DA3D7D4');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873E1A4B8B4E');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D1DA3D7D4');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D53C674EE');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D4B3B0C4C');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D5A2AA3A7');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE professional_profile');
        $this->addSql('DROP TABLE job_request');
        $this->addSql('DROP TABLE offer');
        $this->addSql('DROP TABLE payment');
    }
}

