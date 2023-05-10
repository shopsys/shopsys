<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200214081059 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE promo_code_categories (
                promo_code_id INT NOT NULL,
                category_id INT NOT NULL,
                PRIMARY KEY(promo_code_id, category_id)
            )');
        $this->sql('CREATE INDEX IDX_66ECEA5F2FAE4625 ON promo_code_categories (promo_code_id)');
        $this->sql('CREATE INDEX IDX_66ECEA5F12469DE2 ON promo_code_categories (category_id)');
        $this->sql('
            CREATE TABLE promo_code_products (
                promo_code_id INT NOT NULL,
                product_id INT NOT NULL,
                PRIMARY KEY(promo_code_id, product_id)
            )');
        $this->sql('CREATE INDEX IDX_C970A1712FAE4625 ON promo_code_products (promo_code_id)');
        $this->sql('CREATE INDEX IDX_C970A1714584665A ON promo_code_products (product_id)');
        $this->sql('
            ALTER TABLE
                promo_code_categories
            ADD
                CONSTRAINT FK_66ECEA5F2FAE4625 FOREIGN KEY (promo_code_id) REFERENCES promo_codes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                promo_code_categories
            ADD
                CONSTRAINT FK_66ECEA5F12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                promo_code_products
            ADD
                CONSTRAINT FK_C970A1712FAE4625 FOREIGN KEY (promo_code_id) REFERENCES promo_codes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                promo_code_products
            ADD
                CONSTRAINT FK_C970A1714584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
