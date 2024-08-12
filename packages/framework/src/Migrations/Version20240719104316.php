<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240719104316 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE uploaded_files_relations ADD type VARCHAR(100) NOT NULL');

        $this->sql('INSERT INTO uploaded_files_relations (uploaded_file_id, entity_name, entity_id, position, type)
            SELECT id, entity_name, entity_id, position, type FROM uploaded_files');

        $this->sql('ALTER TABLE uploaded_files DROP COLUMN entity_name');
        $this->sql('ALTER TABLE uploaded_files DROP COLUMN entity_id');
        $this->sql('ALTER TABLE uploaded_files DROP COLUMN position');
        $this->sql('ALTER TABLE uploaded_files DROP COLUMN type');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
