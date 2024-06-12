<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240612124051 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200226073300')) {
            $this->sql('
            CREATE TABLE navigation_items (
                id SERIAL NOT NULL,
                position INT NOT NULL,
                name TEXT NOT NULL,
                url TEXT NOT NULL,
                PRIMARY KEY(id)
            )');
            $this->sql('
            CREATE TABLE navigation_item_categories (
                column_number INT NOT NULL,
                navigation_item_id INT NOT NULL,
                category_id INT NOT NULL,
                position INT NOT NULL,
                PRIMARY KEY(
                    navigation_item_id, column_number,
                    category_id
                )
            )');
            $this->sql('CREATE INDEX IDX_71699B7654ED5C2D ON navigation_item_categories (navigation_item_id)');
            $this->sql('CREATE INDEX IDX_71699B7612469DE2 ON navigation_item_categories (category_id)');
            $this->sql('
            ALTER TABLE
                navigation_item_categories
            ADD
                CONSTRAINT FK_B2A413D84F43D067 FOREIGN KEY (navigation_item_id) REFERENCES navigation_items (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                navigation_item_categories
            ADD
                CONSTRAINT FK_B2A413D812469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        }

        if (!$this->isAppMigrationNotInstalledRemoveIfExists('Version20200424100911')) {
            return;
        }

        $this->sql('ALTER TABLE navigation_items ADD domain_id INT NOT NULL default 1');
        $this->sql('ALTER TABLE navigation_items ALTER domain_id DROP DEFAULT');
        $this->sql('CREATE INDEX domain_id_idx ON navigation_items (domain_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
