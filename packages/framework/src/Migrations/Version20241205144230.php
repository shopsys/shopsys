<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20241205144230 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        //product indexes
        $this->sql('CREATE INDEX products_catnum_trgm_idx ON products USING gin (normalized(catnum) gin_trgm_ops);');
        $this->sql('CREATE INDEX products_partno_trgm_idx ON products USING gin (normalized(partno) gin_trgm_ops);');
        $this->sql('CREATE INDEX product_translations_name_trgm_idx ON product_translations USING gin (normalized(name) gin_trgm_ops);');

        //order indexes
        $this->sql('CREATE INDEX orders_email_trgm_idx ON orders USING gin (normalized(email) gin_trgm_ops);');
        $this->sql('CREATE INDEX orders_last_name_trgm_idx ON orders USING gin (normalized(last_name) gin_trgm_ops);');
        $this->sql('CREATE INDEX orders_company_name_trgm_idx ON orders USING gin (normalized(company_name) gin_trgm_ops);');

        //user indexes
        $this->sql('CREATE INDEX customer_users_email_trgm_idx ON customer_users USING gin (normalized(email) gin_trgm_ops);');
        $this->sql('CREATE INDEX customer_users_last_name_trgm_idx ON customer_users USING gin (normalized(last_name) gin_trgm_ops);');
        $this->sql('CREATE INDEX customer_users_telephone_trgm_idx ON customer_users USING gin (normalized(telephone) gin_trgm_ops);');

        $this->sql('CREATE INDEX billing_addresses_company_name_trgm_idx ON billing_addresses USING gin (normalized(company_name) gin_trgm_ops);');
    }
}
