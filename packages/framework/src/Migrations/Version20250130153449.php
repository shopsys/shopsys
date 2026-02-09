<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250130153449 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql(
            '
            UPDATE customer_user_role_groups
            SET roles = roles::jsonb || \'["ROLE_API_COMPLAINT_CREATION"]\'::jsonb
            WHERE NOT (roles::jsonb @> \'["ROLE_API_ALL"]\'::jsonb);',
        );
    }
}
