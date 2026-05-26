<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20190215092228 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('COMMENT ON COLUMN zbozi_product_domains.cpc IS \'(DC2Type:money)\'');
        $this->sql('COMMENT ON COLUMN zbozi_product_domains.cpc_search IS \'(DC2Type:money)\'');
    }
}
