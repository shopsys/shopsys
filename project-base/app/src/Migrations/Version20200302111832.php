<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200302111832 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE products ADD download_assembly_instruction_files BOOLEAN NOT NULL DEFAULT false');
        $this->sql('ALTER TABLE products ALTER download_assembly_instruction_files DROP DEFAULT;');
        $this->sql('ALTER TABLE products ADD download_product_type_plan_files BOOLEAN NOT NULL DEFAULT false');
        $this->sql('ALTER TABLE products ALTER download_product_type_plan_files DROP DEFAULT;');
        $this->sql('ALTER TABLE product_domains ADD assembly_instruction_code VARCHAR(255) DEFAULT NULL');
        $this->sql('ALTER TABLE product_domains ADD product_type_plan_code VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
