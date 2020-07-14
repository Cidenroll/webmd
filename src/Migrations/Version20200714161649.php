<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200714161649 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE analytic (id INT AUTO_INCREMENT NOT NULL, current_user_id INT NOT NULL, current_user_mail VARCHAR(255) NOT NULL, action VARCHAR(750) NOT NULL, current_route VARCHAR(255) NOT NULL, user_role VARCHAR(255) NOT NULL, user_trace VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE doctor_to_patient (id INT AUTO_INCREMENT NOT NULL, doctor_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE doctor_to_patient_user (doctor_to_patient_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_68A5C38877FEB8D8 (doctor_to_patient_id), INDEX IDX_68A5C388A76ED395 (user_id), PRIMARY KEY(doctor_to_patient_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE map_activity (id INT AUTO_INCREMENT NOT NULL, user_name VARCHAR(255) NOT NULL, mac_address VARCHAR(255) NOT NULL, latitude VARCHAR(255) NOT NULL, longitutde VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE map_log (id INT AUTO_INCREMENT NOT NULL, mac_address VARCHAR(255) NOT NULL, user_name VARCHAR(255) NOT NULL, latitude VARCHAR(255) NOT NULL, longitude VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE processed_files (id INT AUTO_INCREMENT NOT NULL, file_id INT NOT NULL, patient_id INT NOT NULL, last_commenting_doctor_id INT NOT NULL, content LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE relations_dp2 (id INT AUTO_INCREMENT NOT NULL, doctor_id INT NOT NULL, patient_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE relations_pd2 (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, doctor_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, user_type VARCHAR(255) NOT NULL, telephone_number VARCHAR(50) DEFAULT NULL, profile_picture_path VARCHAR(500) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_file (id INT AUTO_INCREMENT NOT NULL, user_id_id INT DEFAULT NULL, file_name VARCHAR(255) NOT NULL, doc_type VARCHAR(255) NOT NULL, doctor_id INT DEFAULT NULL, comment LONGTEXT DEFAULT NULL, file_content LONGBLOB NOT NULL, created_at DATETIME NOT NULL, latest_commented_doctor_id VARCHAR(255) DEFAULT NULL, INDEX IDX_F61E7AD99D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE doctor_to_patient_user ADD CONSTRAINT FK_68A5C38877FEB8D8 FOREIGN KEY (doctor_to_patient_id) REFERENCES doctor_to_patient (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doctor_to_patient_user ADD CONSTRAINT FK_68A5C388A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_file ADD CONSTRAINT FK_F61E7AD99D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE doctor_to_patient_user DROP FOREIGN KEY FK_68A5C38877FEB8D8');
        $this->addSql('ALTER TABLE doctor_to_patient_user DROP FOREIGN KEY FK_68A5C388A76ED395');
        $this->addSql('ALTER TABLE user_file DROP FOREIGN KEY FK_F61E7AD99D86650F');
        $this->addSql('DROP TABLE analytic');
        $this->addSql('DROP TABLE doctor_to_patient');
        $this->addSql('DROP TABLE doctor_to_patient_user');
        $this->addSql('DROP TABLE map_activity');
        $this->addSql('DROP TABLE map_log');
        $this->addSql('DROP TABLE processed_files');
        $this->addSql('DROP TABLE relations_dp2');
        $this->addSql('DROP TABLE relations_pd2');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_file');
    }
}
