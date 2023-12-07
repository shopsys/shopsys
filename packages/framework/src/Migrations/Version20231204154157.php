<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20231204154157 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        if (!$this->isAppMigrationNotInstalledRemoveIfExists('Version20200316101714')) {
            $this->sql('ALTER TABLE products DROP COLUMN preorder');
        }

        if (!$this->isAppMigrationNotInstalledRemoveIfExists('Version20200401074357')) {
            $this->sql('ALTER TABLE products DROP COLUMN vendor_delivery_date');
        }

        $this->sql('DELETE FROM setting_values WHERE name = :name', ['name' => 'deliveryDayOnStock']);
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
