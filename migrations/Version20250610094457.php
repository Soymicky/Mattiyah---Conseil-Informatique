<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250610094457 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE rendez_vous_services (rendez_vous_id INT NOT NULL, services_id INT NOT NULL, INDEX IDX_9B27A34A91EF7EAA (rendez_vous_id), INDEX IDX_9B27A34AAEF5A6C1 (services_id), PRIMARY KEY(rendez_vous_id, services_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous_services ADD CONSTRAINT FK_9B27A34A91EF7EAA FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous_services ADD CONSTRAINT FK_9B27A34AAEF5A6C1 FOREIGN KEY (services_id) REFERENCES services (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis_client DROP INDEX UNIQ_708E90EF91EF7EAA, ADD INDEX IDX_708E90EF91EF7EAA (rendez_vous_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis_client ADD service_id INT DEFAULT NULL, CHANGE rendez_vous_id rendez_vous_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis_client ADD CONSTRAINT FK_708E90EFED5CA9E6 FOREIGN KEY (service_id) REFERENCES services (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_708E90EFED5CA9E6 ON avis_client (service_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous_services DROP FOREIGN KEY FK_9B27A34A91EF7EAA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous_services DROP FOREIGN KEY FK_9B27A34AAEF5A6C1
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE rendez_vous_services
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis_client DROP INDEX IDX_708E90EF91EF7EAA, ADD UNIQUE INDEX UNIQ_708E90EF91EF7EAA (rendez_vous_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis_client DROP FOREIGN KEY FK_708E90EFED5CA9E6
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_708E90EFED5CA9E6 ON avis_client
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis_client DROP service_id, CHANGE rendez_vous_id rendez_vous_id INT NOT NULL
        SQL);
    }
}
