<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250124125113 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql(
            '
            UPDATE customer_user_role_groups
            SET roles = roles::jsonb || \'["ROLE_API_CART_AND_ORDER_CREATION"]\'::jsonb
            WHERE NOT (roles::jsonb @> \'["ROLE_API_ALL"]\'::jsonb);',
        );
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
