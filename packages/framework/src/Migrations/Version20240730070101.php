<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20240730070101 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE billing_addresses ALTER country_id DROP NOT NULL');
        $this->sql('ALTER TABLE billing_addresses ALTER street DROP NOT NULL');
        $this->sql('ALTER TABLE billing_addresses ALTER city DROP NOT NULL');
        $this->sql('ALTER TABLE billing_addresses ALTER postcode DROP NOT NULL');
    }
}
