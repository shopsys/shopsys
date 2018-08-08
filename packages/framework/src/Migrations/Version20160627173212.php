<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20160627173212 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('CREATE TABLE newsletter_subscribers (email VARCHAR(255) NOT NULL, PRIMARY KEY(email));');
    }

    public function down(Schema $schema)
    {
    }
}
