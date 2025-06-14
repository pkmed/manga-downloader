<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250614151512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE manga ADD manga_source VARCHAR(255) NOT NULL, ADD slug_url VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX manga_source_slug_url_uq ON manga (manga_source, slug_url)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX manga_source_slug_url_uq ON manga');
        $this->addSql('ALTER TABLE manga DROP manga_source, DROP slug_url');
    }
}
