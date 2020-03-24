<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200317124724 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('SELECT SETVAL(pg_get_serial_sequence(\'product_types\', \'id\'), COALESCE((SELECT MAX(id) FROM product_types) + 1, 1), false)');
        $this->sql('SELECT SETVAL(pg_get_serial_sequence(\'product_type_translations\', \'id\'), COALESCE((SELECT MAX(id) FROM product_type_translations) + 1, 1), false)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
