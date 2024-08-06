<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240806084610 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE customer_uploaded_files (
                id SERIAL NOT NULL,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL,
                entity_name VARCHAR(100) NOT NULL,
                entity_id INT NOT NULL,
                extension VARCHAR(5) NOT NULL,
                modified_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                type VARCHAR(100) NOT NULL,
                position INT NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_970DDB7F16EFC72D81257D5D ON customer_uploaded_files (entity_name, entity_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
