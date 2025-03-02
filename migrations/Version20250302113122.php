<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250302113122 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE manga_chapter (id INT AUTO_INCREMENT NOT NULL, manga_id INT NOT NULL, name VARCHAR(255) NOT NULL, volume SMALLINT NOT NULL, number SMALLINT NOT NULL, page_count SMALLINT NOT NULL, INDEX IDX_6736602F7B6461 (manga_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE manga_chapter ADD CONSTRAINT FK_6736602F7B6461 FOREIGN KEY (manga_id) REFERENCES manga (id)');
        $this->addSql('ALTER TABLE manga ADD chapters_count SMALLINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE manga_chapter DROP FOREIGN KEY FK_6736602F7B6461');
        $this->addSql('DROP TABLE manga_chapter');
        $this->addSql('ALTER TABLE manga DROP chapters_count');
    }
}
