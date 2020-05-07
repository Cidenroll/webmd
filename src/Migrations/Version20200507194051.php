<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200507194051 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_68A5C388A76ED395');
        $this->addSql('DROP INDEX IDX_68A5C38877FEB8D8');
        $this->addSql('CREATE TEMPORARY TABLE __temp__doctor_to_patient_user AS SELECT doctor_to_patient_id, user_id FROM doctor_to_patient_user');
        $this->addSql('DROP TABLE doctor_to_patient_user');
        $this->addSql('CREATE TABLE doctor_to_patient_user (doctor_to_patient_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(doctor_to_patient_id, user_id), CONSTRAINT FK_68A5C38877FEB8D8 FOREIGN KEY (doctor_to_patient_id) REFERENCES doctor_to_patient (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_68A5C388A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO doctor_to_patient_user (doctor_to_patient_id, user_id) SELECT doctor_to_patient_id, user_id FROM __temp__doctor_to_patient_user');
        $this->addSql('DROP TABLE __temp__doctor_to_patient_user');
        $this->addSql('CREATE INDEX IDX_68A5C388A76ED395 ON doctor_to_patient_user (user_id)');
        $this->addSql('CREATE INDEX IDX_68A5C38877FEB8D8 ON doctor_to_patient_user (doctor_to_patient_id)');
        $this->addSql('ALTER TABLE user ADD COLUMN telephone_number VARCHAR(50) DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_F61E7AD99D86650F');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user_file AS SELECT id, user_id_id, file_name, doc_type, doctor_id, comment FROM user_file');
        $this->addSql('DROP TABLE user_file');
        $this->addSql('CREATE TABLE user_file (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id_id INTEGER DEFAULT NULL, file_name VARCHAR(255) NOT NULL COLLATE BINARY, doc_type VARCHAR(255) NOT NULL COLLATE BINARY, doctor_id INTEGER DEFAULT NULL, comment CLOB DEFAULT NULL COLLATE BINARY, CONSTRAINT FK_F61E7AD99D86650F FOREIGN KEY (user_id_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user_file (id, user_id_id, file_name, doc_type, doctor_id, comment) SELECT id, user_id_id, file_name, doc_type, doctor_id, comment FROM __temp__user_file');
        $this->addSql('DROP TABLE __temp__user_file');
        $this->addSql('CREATE INDEX IDX_F61E7AD99D86650F ON user_file (user_id_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_68A5C38877FEB8D8');
        $this->addSql('DROP INDEX IDX_68A5C388A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__doctor_to_patient_user AS SELECT doctor_to_patient_id, user_id FROM doctor_to_patient_user');
        $this->addSql('DROP TABLE doctor_to_patient_user');
        $this->addSql('CREATE TABLE doctor_to_patient_user (doctor_to_patient_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(doctor_to_patient_id, user_id))');
        $this->addSql('INSERT INTO doctor_to_patient_user (doctor_to_patient_id, user_id) SELECT doctor_to_patient_id, user_id FROM __temp__doctor_to_patient_user');
        $this->addSql('DROP TABLE __temp__doctor_to_patient_user');
        $this->addSql('CREATE INDEX IDX_68A5C38877FEB8D8 ON doctor_to_patient_user (doctor_to_patient_id)');
        $this->addSql('CREATE INDEX IDX_68A5C388A76ED395 ON doctor_to_patient_user (user_id)');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password, user_type FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, user_type VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO user (id, email, roles, password, user_type) SELECT id, email, roles, password, user_type FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('DROP INDEX IDX_F61E7AD99D86650F');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user_file AS SELECT id, user_id_id, file_name, doc_type, doctor_id, comment FROM user_file');
        $this->addSql('DROP TABLE user_file');
        $this->addSql('CREATE TABLE user_file (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id_id INTEGER DEFAULT NULL, file_name VARCHAR(255) NOT NULL, doc_type VARCHAR(255) NOT NULL, doctor_id INTEGER DEFAULT NULL, comment CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO user_file (id, user_id_id, file_name, doc_type, doctor_id, comment) SELECT id, user_id_id, file_name, doc_type, doctor_id, comment FROM __temp__user_file');
        $this->addSql('DROP TABLE __temp__user_file');
        $this->addSql('CREATE INDEX IDX_F61E7AD99D86650F ON user_file (user_id_id)');
    }
}
