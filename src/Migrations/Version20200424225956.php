<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200424225956 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE doctor_to_patient (id INT AUTO_INCREMENT NOT NULL, doctor_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE doctor_to_patient_user (doctor_to_patient_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_68A5C38877FEB8D8 (doctor_to_patient_id), INDEX IDX_68A5C388A76ED395 (user_id), PRIMARY KEY(doctor_to_patient_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE doctor_to_patient_user ADD CONSTRAINT FK_68A5C38877FEB8D8 FOREIGN KEY (doctor_to_patient_id) REFERENCES doctor_to_patient (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doctor_to_patient_user ADD CONSTRAINT FK_68A5C388A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE doctor_to_patient_user DROP FOREIGN KEY FK_68A5C38877FEB8D8');
        $this->addSql('DROP TABLE doctor_to_patient');
        $this->addSql('DROP TABLE doctor_to_patient_user');
    }
}
