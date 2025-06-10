<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250610120412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE avis_client ADD niveau_service_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis_client ADD CONSTRAINT FK_708E90EFB9D02F2B FOREIGN KEY (niveau_service_id) REFERENCES niveau_service (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_708E90EFB9D02F2B ON avis_client (niveau_service_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE niveau_service ADD description VARCHAR(255) DEFAULT NULL, ADD prix DOUBLE PRECISION DEFAULT NULL, CHANGE nom_niveau type_service VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE avis_client DROP FOREIGN KEY FK_708E90EFB9D02F2B
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_708E90EFB9D02F2B ON avis_client
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis_client DROP niveau_service_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE niveau_service ADD nom_niveau VARCHAR(255) DEFAULT NULL, DROP type_service, DROP description, DROP prix
        SQL);
    }
}
