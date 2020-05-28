<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200527192118 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX idx_current_user_mail ON analytic');
        $this->addSql('DROP INDEX idx_created_at ON analytic');
        $this->addSql('DROP INDEX idx_user_role ON analytic');
        $this->addSql('DROP INDEX idx_current_user_id ON analytic');
        $this->addSql('ALTER TABLE user_file ADD created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX idx_current_user_mail ON analytic (current_user_mail(191))');
        $this->addSql('CREATE INDEX idx_created_at ON analytic (created_at)');
        $this->addSql('CREATE INDEX idx_user_role ON analytic (user_role(191))');
        $this->addSql('CREATE INDEX idx_current_user_id ON analytic (current_user_id)');
        $this->addSql('ALTER TABLE user_file DROP created_at');
    }
}
