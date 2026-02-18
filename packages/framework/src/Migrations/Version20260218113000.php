<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20260218113000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql(
            "UPDATE setting_values
                SET value = TRIM(REGEXP_REPLACE(value, E'[[:space:]]*[\\r\\n]+[[:space:]]*', ' ', 'g'))
                WHERE name = :name
                    AND (POSITION(CHR(13) IN value) > 0 OR POSITION(CHR(10) IN value) > 0)",
            [
                'name' => 'cspHeader',
            ],
        );
    }
}
