<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20180323092028 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->sql('ALTER TABLE images ADD original_hash_image VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
