<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20180730135725 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql(
            'CREATE OR REPLACE FUNCTION field(integer, integer[])
            RETURNS integer AS
            $$
            SELECT COALESCE(( SELECT i FROM generate_subscripts($2, 1) gs(i) WHERE $2[i] = $1 ), 0)
            $$
            LANGUAGE SQL STABLE',
        );
    }
}
