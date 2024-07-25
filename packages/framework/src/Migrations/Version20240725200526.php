<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240725200526 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE INDEX IDX_5474CBC416EFC72D81257D5D8CDE5729 ON uploaded_files_relations (entity_name, entity_id, type)');
        $this->sql('
            CREATE INDEX IDX_5474CBC416EFC72D81257D5D8CDE5729276973A0 ON uploaded_files_relations (
                entity_name, entity_id, type, uploaded_file_id
            )');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
