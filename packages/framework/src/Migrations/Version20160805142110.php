<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20160805142110 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE product_domains ADD show_in_zbozi_feed BOOLEAN NOT NULL DEFAULT TRUE');
        $this->sql('ALTER TABLE product_domains ALTER show_in_zbozi_feed DROP DEFAULT');

        $this->sql('ALTER TABLE product_domains ADD zbozi_cpc NUMERIC(16, 2) DEFAULT NULL');
        $this->sql('ALTER TABLE product_domains ADD zbozi_cpc_search NUMERIC(16, 2) DEFAULT NULL');
    }
}
