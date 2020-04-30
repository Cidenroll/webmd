<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200430000301 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE doctor_to_patient (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, doctor_id INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE doctor_to_patient_user (doctor_to_patient_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(doctor_to_patient_id, user_id))');
        $this->addSql('CREATE INDEX IDX_68A5C38877FEB8D8 ON doctor_to_patient_user (doctor_to_patient_id)');
        $this->addSql('CREATE INDEX IDX_68A5C388A76ED395 ON doctor_to_patient_user (user_id)');
        $this->addSql('CREATE TABLE relations_dp2 (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, doctor_id INTEGER NOT NULL, patient_id INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE relations_pd2 (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, patient_id INTEGER NOT NULL, doctor_id INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, user_type VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('CREATE TABLE user_file (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id_id INTEGER DEFAULT NULL, file_name VARCHAR(255) NOT NULL, doc_type VARCHAR(255) NOT NULL, doctor_id INTEGER DEFAULT NULL, comment CLOB DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_F61E7AD99D86650F ON user_file (user_id_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE doctor_to_patient');
        $this->addSql('DROP TABLE doctor_to_patient_user');
        $this->addSql('DROP TABLE relations_dp2');
        $this->addSql('DROP TABLE relations_pd2');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_file');
    }
}
