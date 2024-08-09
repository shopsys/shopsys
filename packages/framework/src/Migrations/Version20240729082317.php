<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240729082317 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE billing_addresses ADD uuid UUID NOT NULL DEFAULT uuid_generate_v4()');
        $this->sql('ALTER TABLE billing_addresses ALTER uuid DROP DEFAULT');
        $this->sql('CREATE UNIQUE INDEX UNIQ_DBD91748D17F50A6 ON billing_addresses (uuid)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
