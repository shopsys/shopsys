<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200608065837 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE products ADD default_variant_id INT DEFAULT NULL');
        $this->sql('
            ALTER TABLE
                products
            ADD
                CONSTRAINT FK_B3BA5A5A734AFDCC FOREIGN KEY (default_variant_id) REFERENCES products (id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE UNIQUE INDEX UNIQ_B3BA5A5A734AFDCC ON products (default_variant_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
