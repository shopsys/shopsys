<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20250313074307 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('CREATE INDEX IDX_64EC05ABF3667F8381257D5D115F0EE5BF28CD64 
            ON friendly_urls (route_name, entity_id, domain_id, main)');
    }
}
