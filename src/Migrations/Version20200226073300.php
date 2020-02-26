<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200226073300 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE horizontal_menu_items (
                id SERIAL NOT NULL,
                position INT NOT NULL,
                name TEXT NOT NULL,
                url TEXT NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('
            CREATE TABLE horizontal_menu_item_categories (
                column_number INT NOT NULL,
                horizontal_menu_item_id INT NOT NULL,
                category_id INT NOT NULL,
                position INT NOT NULL,
                PRIMARY KEY(
                    horizontal_menu_item_id, column_number,
                    category_id
                )
            )');
        $this->sql('CREATE INDEX IDX_B2A413D84F43D067 ON horizontal_menu_item_categories (horizontal_menu_item_id)');
        $this->sql('CREATE INDEX IDX_B2A413D812469DE2 ON horizontal_menu_item_categories (category_id)');
        $this->sql('
            ALTER TABLE
                horizontal_menu_item_categories
            ADD
                CONSTRAINT FK_B2A413D84F43D067 FOREIGN KEY (horizontal_menu_item_id) REFERENCES horizontal_menu_items (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                horizontal_menu_item_categories
            ADD
                CONSTRAINT FK_B2A413D812469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
