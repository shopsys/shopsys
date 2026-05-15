<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20180530131622 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE product_domains DROP CONSTRAINT "product_domains_pkey"');
        $this->sql('ALTER TABLE product_domains ADD id SERIAL NOT NULL');
        $this->sql('ALTER TABLE product_domains ADD PRIMARY KEY (id)');
        $this->sql('CREATE UNIQUE INDEX product_domain ON product_domains (product_id, domain_id)');
    }
}
