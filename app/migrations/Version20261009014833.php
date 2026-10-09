<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261009014833 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rol (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(60) NOT NULL, descripcion VARCHAR(100) DEFAULT NULL, estado VARCHAR(1) NOT NULL, fecha_creacion DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE usuario (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nombre_completo VARCHAR(120) NOT NULL, correo VARCHAR(120) NOT NULL, estado VARCHAR(1) NOT NULL, fecha_creacion DATETIME NOT NULL, rol_id INT NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), INDEX IDX_2265B05D4BAB96C (rol_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE usuario ADD CONSTRAINT FK_2265B05D4BAB96C FOREIGN KEY (rol_id) REFERENCES rol (id)');
        $this->addSql('ALTER TABLE actividad CHANGE motivo_cancelación motivo_cancelacion LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE usuario DROP FOREIGN KEY FK_2265B05D4BAB96C');
        $this->addSql('DROP TABLE rol');
        $this->addSql('DROP TABLE usuario');
        $this->addSql('ALTER TABLE actividad CHANGE motivo_cancelacion motivo_cancelación LONGTEXT DEFAULT NULL');
    }
}
