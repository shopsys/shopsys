<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240318162418 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $postgresqlVersion = $this->sql('SELECT version();')->fetchOne();
        $postgresqlVersion = substr($postgresqlVersion, 11, 2);
        $existsNormalizeFunction = $this->sql('SELECT 1 FROM pg_proc WHERE proname = \'normalize\'')->fetchOne();

        if ($postgresqlVersion > 12 || $existsNormalizeFunction !== 1) {
            return;
        }

        $this->sql('ALTER FUNCTION normalize(text) RENAME TO normalized;');

        $this->editDefaultDbIndexes();
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }

    private function editDefaultDbIndexes(): void
    {
        $this->sql('DROP INDEX IF EXISTS product_translations_name_normalize_idx;');
        $this->sql('DROP INDEX IF EXISTS product_catnum_normalize_idx;');
        $this->sql('DROP INDEX IF EXISTS product_partno_normalize_idx;');
        $this->sql('DROP INDEX IF EXISTS order_email_normalize_idx;');
        $this->sql('DROP INDEX IF EXISTS order_last_name_normalize_idx;');
        $this->sql('DROP INDEX IF EXISTS order_company_name_normalize_idx;');

        $this->sql('CREATE INDEX IF NOT EXISTS product_translations_name_normalized_idx
            ON product_translations (NORMALIZED(name))');
        $this->sql('CREATE INDEX IF NOT EXISTS product_catnum_normalized_idx
            ON products (NORMALIZED(catnum))');
        $this->sql('CREATE INDEX IF NOT EXISTS product_partno_normalized_idx
            ON products (NORMALIZED(partno))');
        $this->sql('CREATE INDEX IF NOT EXISTS order_email_normalized_idx
            ON orders (NORMALIZED(email))');
        $this->sql('CREATE INDEX IF NOT EXISTS order_last_name_normalized_idx
            ON orders (NORMALIZED(last_name))');
        $this->sql('CREATE INDEX IF NOT EXISTS order_company_name_normalized_idx
            ON orders (NORMALIZED(company_name))');
    }
}
