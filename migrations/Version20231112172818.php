<?php

declare(strict_types=1);

namespace Paragin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231112172818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answer (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, question_id INTEGER DEFAULT NULL, student_id INTEGER DEFAULT NULL, source_filename VARCHAR(255) DEFAULT NULL, points INTEGER NOT NULL, CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_DADD4A25CB944F1A FOREIGN KEY (student_id) REFERENCES student (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_DADD4A251E27F6BF ON answer (question_id)');
        $this->addSql('CREATE INDEX IDX_DADD4A25CB944F1A ON answer (student_id)');
        $this->addSql('CREATE TABLE exam (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, source_filename VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE question (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, exam_id INTEGER DEFAULT NULL, max_points INTEGER NOT NULL, name VARCHAR(255) NOT NULL, CONSTRAINT FK_B6F7494E578D5E91 FOREIGN KEY (exam_id) REFERENCES exam (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B6F7494E578D5E91 ON question (exam_id)');
        $this->addSql('CREATE TABLE student (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE answer');
        $this->addSql('DROP TABLE exam');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE student');
    }
}
