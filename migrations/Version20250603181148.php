<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250603181148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE rendez_vous_service (id INT AUTO_INCREMENT NOT NULL, rendez_vous_id INT DEFAULT NULL, services_id INT DEFAULT NULL, niveau_service_id INT DEFAULT NULL, INDEX IDX_4C0B8A9591EF7EAA (rendez_vous_id), INDEX IDX_4C0B8A95AEF5A6C1 (services_id), INDEX IDX_4C0B8A95B9D02F2B (niveau_service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous_service ADD CONSTRAINT FK_4C0B8A9591EF7EAA FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous_service ADD CONSTRAINT FK_4C0B8A95AEF5A6C1 FOREIGN KEY (services_id) REFERENCES services (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous_service ADD CONSTRAINT FK_4C0B8A95B9D02F2B FOREIGN KEY (niveau_service_id) REFERENCES niveau_service (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous_service DROP FOREIGN KEY FK_4C0B8A9591EF7EAA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous_service DROP FOREIGN KEY FK_4C0B8A95AEF5A6C1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous_service DROP FOREIGN KEY FK_4C0B8A95B9D02F2B
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE rendez_vous_service
        SQL);
    }
}
