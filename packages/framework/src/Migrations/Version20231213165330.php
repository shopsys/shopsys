<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20231213165330 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200909112133')) {
            $this->sql('ALTER TABLE transports ADD days_until_delivery INT NOT NULL DEFAULT 0');
            $this->sql('ALTER TABLE transports ALTER days_until_delivery DROP DEFAULT');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200407071420')) {
            $this->sql('ALTER TABLE product_domains ADD sale_exclusion BOOLEAN NOT NULL DEFAULT FALSE');
            $this->sql('ALTER TABLE product_domains ALTER sale_exclusion DROP DEFAULT;');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200618125834')) {
            $this->sql('ALTER TABLE product_domains ADD domain_hidden BOOLEAN NOT NULL DEFAULT FALSE');
            $this->sql('ALTER TABLE product_domains ALTER domain_hidden DROP DEFAULT');
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
