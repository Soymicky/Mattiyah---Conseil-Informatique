<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250603140332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE avis_client (id INT AUTO_INCREMENT NOT NULL, rendez_vous_id INT NOT NULL, utilisateur_id INT NOT NULL, titre VARCHAR(255) DEFAULT NULL, note INT DEFAULT NULL, date DATETIME DEFAULT NULL, commentaire LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_708E90EF91EF7EAA (rendez_vous_id), INDEX IDX_708E90EFFB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE niveau_service (id INT AUTO_INCREMENT NOT NULL, nom_niveau VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE rendez_vous (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, niveau_service_id INT NOT NULL, date_rdv DATETIME DEFAULT NULL, statut VARCHAR(50) DEFAULT NULL, INDEX IDX_65E8AA0AFB88E14F (utilisateur_id), INDEX IDX_65E8AA0AB9D02F2B (niveau_service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE rendez_vous_services (rendez_vous_id INT NOT NULL, services_id INT NOT NULL, INDEX IDX_9B27A34A91EF7EAA (rendez_vous_id), INDEX IDX_9B27A34AAEF5A6C1 (services_id), PRIMARY KEY(rendez_vous_id, services_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE services (id INT AUTO_INCREMENT NOT NULL, niveau_service_id INT NOT NULL, nom_service VARCHAR(255) DEFAULT NULL, description_service VARCHAR(255) DEFAULT NULL, type_offre VARCHAR(255) DEFAULT NULL, INDEX IDX_7332E169B9D02F2B (niveau_service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) DEFAULT NULL, prenom VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, telephone VARCHAR(20) DEFAULT NULL, mot_de_passe VARCHAR(255) DEFAULT NULL, statut VARCHAR(50) DEFAULT NULL, dt_modification DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis_client ADD CONSTRAINT FK_708E90EF91EF7EAA FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis_client ADD CONSTRAINT FK_708E90EFFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0AFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0AB9D02F2B FOREIGN KEY (niveau_service_id) REFERENCES niveau_service (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous_services ADD CONSTRAINT FK_9B27A34A91EF7EAA FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous_services ADD CONSTRAINT FK_9B27A34AAEF5A6C1 FOREIGN KEY (services_id) REFERENCES services (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE services ADD CONSTRAINT FK_7332E169B9D02F2B FOREIGN KEY (niveau_service_id) REFERENCES niveau_service (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE avis_client DROP FOREIGN KEY FK_708E90EF91EF7EAA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis_client DROP FOREIGN KEY FK_708E90EFFB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0AFB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0AB9D02F2B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous_services DROP FOREIGN KEY FK_9B27A34A91EF7EAA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez_vous_services DROP FOREIGN KEY FK_9B27A34AAEF5A6C1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE services DROP FOREIGN KEY FK_7332E169B9D02F2B
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE avis_client
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE niveau_service
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE rendez_vous
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE rendez_vous_services
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE services
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE utilisateur
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
