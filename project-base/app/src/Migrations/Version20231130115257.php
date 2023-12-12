<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20231130115257 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE product_domains DROP assembly_instruction_code');
        $this->sql('ALTER TABLE product_domains DROP product_type_plan_code');
        $this->sql('ALTER TABLE products DROP download_assembly_instruction_files');
        $this->sql('ALTER TABLE products DROP download_product_type_plan_files');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
