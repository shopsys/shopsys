<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240103095628 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE entity_log (
                id SERIAL NOT NULL,
                action VARCHAR(255) NOT NULL,
                user_identifier VARCHAR(255) NOT NULL,
                entity_name VARCHAR(255) NOT NULL,
                entity_id INT NOT NULL,
                entity_identifier VARCHAR(255) NOT NULL,
                source VARCHAR(255) NOT NULL,
                change_set JSON NOT NULL,
                parent_entity_name VARCHAR(255) DEFAULT NULL,
                parent_entity_id INT DEFAULT NULL,
                log_collection_number VARCHAR(50) NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )');

        $this->sql('CREATE INDEX IDX_F1B0086216EFC72D ON entity_log (entity_name)');
        $this->sql('CREATE INDEX IDX_F1B0086281257D5D ON entity_log (entity_id)');
        $this->sql('CREATE INDEX IDX_F1B008627DC3D55D ON entity_log (parent_entity_name)');
        $this->sql('CREATE INDEX IDX_F1B00862706E52B3 ON entity_log (parent_entity_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
