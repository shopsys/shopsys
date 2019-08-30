<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20190823110846 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE products ALTER selling_from TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->sql('ALTER TABLE products ALTER selling_from DROP DEFAULT');
        $this->sql('ALTER TABLE products ALTER selling_to TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->sql('ALTER TABLE products ALTER selling_to DROP DEFAULT');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
