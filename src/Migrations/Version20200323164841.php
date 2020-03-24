<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200323164841 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE category_product_series (
                product_series INT NOT NULL,
                category_id INT NOT NULL,
                position INT NOT NULL,
                PRIMARY KEY(category_id, product_series)
            )');
        $this->sql('CREATE INDEX IDX_B97C03B412469DE2 ON category_product_series (category_id)');
        $this->sql('
            ALTER TABLE
                category_product_series
            ADD
                CONSTRAINT FK_B97C03B412469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
