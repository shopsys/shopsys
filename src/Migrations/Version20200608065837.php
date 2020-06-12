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

        $this->solveDefaultVariants();
    }

    private function solveDefaultVariants(): void
    {
        $mainProductsData = $this->sql('SELECT id FROM products as p WHERE variant_type = \'main\'');
        foreach ($mainProductsData->fetchAll() as $mainProductData) {
            $mainProductId = $mainProductData['id'];

            $defaultVariantId = null;
            $firstVariantId = null;

            $productVariantsData = $this->sql(sprintf('SELECT id, default_variant_id FROM products WHERE main_variant_id = %d AND variant_type = \'variant\'', $mainProductId));
            foreach ($productVariantsData->fetchAll() as $productData) {
                if ($firstVariantId === null) {
                    $firstVariantId = $productData['id'];
                }
                if ($productData['default_variant_id'] !== null) {
                    $defaultVariantId = $productData['default_variant_id'];
                    break;
                }
            }

            if ($defaultVariantId === null) {
                $this->sql(sprintf('UPDATE products SET default_variant_id = %d WHERE id = %d', $firstVariantId, $mainProductId));
            }
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
