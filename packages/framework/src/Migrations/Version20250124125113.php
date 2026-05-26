<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20250124125113 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql(
            '
            UPDATE customer_user_role_groups
            SET roles = roles::jsonb || \'["ROLE_API_CART_AND_ORDER_CREATION"]\'::jsonb
            WHERE NOT (roles::jsonb @> \'["ROLE_API_ALL"]\'::jsonb);',
        );
    }
}
