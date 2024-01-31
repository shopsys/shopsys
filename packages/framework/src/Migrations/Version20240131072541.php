<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240131072541 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->dropTrigger('recalc_catnum_tsvector', 'products');
        $this->dropTrigger('recalc_description_tsvector', 'product_domains');
        $this->dropTrigger('recalc_name_tsvector', 'product_translations');
        $this->dropTrigger('recalc_partno_tsvector', 'products');
        $this->dropTrigger('recalc_product_domain_fulltext_tsvector', 'product_domains');
        $this->dropTrigger('recalc_product_domain_fulltext_tsvector', 'product_translations');
        $this->dropTrigger('recalc_product_domain_fulltext_tsvector', 'products');

        $this->dropFunction('set_product_catnum_tsvector');
        $this->dropFunction('set_product_domain_description_tsvector');
        $this->dropFunction('set_product_domain_fulltext_tsvector');
        $this->dropFunction('set_product_partno_tsvector');
        $this->dropFunction('set_product_translation_name_tsvector');
        $this->dropFunction('update_product_domain_fulltext_tsvector_by_product');
        $this->dropFunction('update_product_domain_fulltext_tsvector_by_product_translation');

        $this->dropColumn('catnum_tsvector', 'products');
        $this->dropColumn('partno_tsvector', 'products');
        $this->dropColumn('description_tsvector', 'product_domains');
        $this->dropColumn('fulltext_tsvector', 'product_domains');
        $this->dropColumn('name_tsvector', 'product_translations');
    }

    /**
     * @param string $triggerName
     * @param string $tableName
     */
    private function dropTrigger(string $triggerName, string $tableName): void
    {
        $this->sql(sprintf('DROP TRIGGER IF EXISTS %s ON %s;', $triggerName, $tableName));
    }

    /**
     * @param string $functionName
     */
    private function dropFunction(string $functionName): void
    {
        $this->sql(sprintf('DROP FUNCTION IF EXISTS %s;', $functionName));
    }

    /**
     * @param string $columnName
     * @param string $tableName
     */
    private function dropColumn(string $columnName, string $tableName): void
    {
        $this->sql(sprintf('ALTER TABLE %s DROP COLUMN IF EXISTS %s;', $tableName, $columnName));
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
