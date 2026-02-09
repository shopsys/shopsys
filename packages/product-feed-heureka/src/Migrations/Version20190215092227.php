<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20190215092227 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('COMMENT ON COLUMN heureka_product_domains.cpc IS \'(DC2Type:money)\'');
    }
}
