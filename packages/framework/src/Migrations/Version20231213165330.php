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

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200313082319')) {
            $this->sql('
            CREATE TABLE category_parameters (
                category_id INT NOT NULL,
                parameter_id INT NOT NULL,
                PRIMARY KEY(category_id, parameter_id)
            )');
            $this->sql('CREATE INDEX IDX_208D188012469DE2 ON category_parameters (category_id)');
            $this->sql('CREATE INDEX IDX_208D18807C56DBD6 ON category_parameters (parameter_id)');
            $this->sql('
            ALTER TABLE
                category_parameters
            ADD
                CONSTRAINT FK_208D188012469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                category_parameters
            ADD
                CONSTRAINT FK_208D18807C56DBD6 FOREIGN KEY (parameter_id) REFERENCES parameters (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200814063510')) {
            $this->sql('ALTER TABLE category_parameters ADD position INT NOT NULL DEFAULT 1');
            $this->sql('ALTER TABLE category_parameters ALTER position DROP DEFAULT');
            $this->sql('CREATE INDEX ordering_idx ON category_parameters (position)');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200422093836')) {
            $this->sql('ALTER TABLE category_parameters ADD collapsed BOOLEAN NOT NULL DEFAULT FALSE');
            $this->sql('ALTER TABLE category_parameters ALTER collapsed DROP DEFAULT');
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
