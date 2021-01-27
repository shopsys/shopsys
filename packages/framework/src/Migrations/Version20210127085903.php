<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20210127085903 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('UPDATE category_translations SET name = NULL WHERE TRIM(name) = \'\'');

        $this->sql('UPDATE payment_translations SET name = NULL WHERE TRIM(name) = \'\'');
        $this->sql('UPDATE payment_translations SET description = NULL WHERE TRIM(description) = \'\'');
        $this->sql('UPDATE payment_translations SET instructions = NULL WHERE TRIM(instructions) = \'\'');

        $this->sql('UPDATE brand_translations SET description = NULL WHERE TRIM(description) = \'\'');

        $this->sql('UPDATE parameter_translations SET name = NULL WHERE TRIM(name) = \'\'');

        $this->sql('UPDATE product_translations SET name = NULL WHERE TRIM(name) = \'\'');
        $this->sql('UPDATE product_translations SET variant_alias = NULL WHERE TRIM(variant_alias) = \'\'');

        $this->sql('UPDATE transport_translations SET name = NULL WHERE TRIM(name) = \'\'');
        $this->sql('UPDATE transport_translations SET description = NULL WHERE TRIM(description) = \'\'');
        $this->sql('UPDATE transport_translations SET instructions = NULL WHERE TRIM(instructions) = \'\'');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
