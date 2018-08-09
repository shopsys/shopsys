<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20180306094316 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('
            CREATE TABLE personal_data_access_request (
                id SERIAL NOT NULL,
                email VARCHAR(255) NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                hash VARCHAR(255) NOT NULL,
                domain_id INT NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE UNIQUE INDEX UNIQ_C84FBB18D1B862B8 ON personal_data_access_request (hash)');
    }

    public function down(Schema $schema)
    {
    }
}
