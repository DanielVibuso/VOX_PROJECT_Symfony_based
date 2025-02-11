<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240725214618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE company_partner (company_id INT NOT NULL, partner_id INT NOT NULL, PRIMARY KEY(company_id, partner_id))');
        $this->addSql('CREATE INDEX IDX_117924C1979B1AD6 ON company_partner (company_id)');
        $this->addSql('CREATE INDEX IDX_117924C19393F8FE ON company_partner (partner_id)');
        $this->addSql('CREATE TABLE users (id INT NOT NULL, email VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE company_partner ADD CONSTRAINT FK_117924C1979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE company_partner ADD CONSTRAINT FK_117924C19393F8FE FOREIGN KEY (partner_id) REFERENCES partners (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $hashedPassword = password_hash('adminpassword', PASSWORD_BCRYPT);
        $this->addSql('
            INSERT INTO users (id, email, password, roles)
            VALUES (nextval(\'users_id_seq\'), \'admin@example.com\', \''.$hashedPassword.'\', \'["ROLE_ADMIN"]\')
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE company_partner DROP CONSTRAINT FK_117924C1979B1AD6');
        $this->addSql('ALTER TABLE company_partner DROP CONSTRAINT FK_117924C19393F8FE');
        $this->addSql('DROP TABLE company_partner');
        $this->addSql('DROP TABLE users');
    }
}
