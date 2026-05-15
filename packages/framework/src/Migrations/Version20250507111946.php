<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20250507111946 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE navigation_items ADD route_name VARCHAR(255) DEFAULT NULL');
        $this->sql('UPDATE navigation_items ni
            SET route_name = fu.route_name
            FROM friendly_urls fu
            WHERE
                fu.domain_id = ni.domain_id
                AND fu.slug = TRIM(BOTH \'/\' FROM ni.url);
        ');
    }
}
