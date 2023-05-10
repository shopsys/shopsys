<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20220713071440 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('UPDATE billing_addresses SET street = \'\' WHERE street IS NULL');
        $this->sql('ALTER TABLE billing_addresses ALTER street SET NOT NULL');

        $this->sql('UPDATE billing_addresses SET city = \'\' WHERE city IS NULL');
        $this->sql('ALTER TABLE billing_addresses ALTER city SET NOT NULL');

        $this->sql('UPDATE billing_addresses SET postcode = \'\' WHERE postcode IS NULL');
        $this->sql('ALTER TABLE billing_addresses ALTER postcode SET NOT NULL');

        $this->sql('UPDATE billing_addresses SET country_id = (SELECT min(id) FROM countries) WHERE country_id IS NULL');
        $this->sql('ALTER TABLE billing_addresses ALTER country_id SET NOT NULL');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
