<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20220725104726 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('UPDATE delivery_addresses SET first_name = \'\' WHERE first_name IS NULL');
        $this->sql('ALTER TABLE delivery_addresses ALTER first_name SET NOT NULL');
        $this->sql('UPDATE delivery_addresses SET last_name = \'\' WHERE last_name IS NULL');
        $this->sql('ALTER TABLE delivery_addresses ALTER last_name SET NOT NULL');
        $this->sql('UPDATE delivery_addresses SET street = \'\' WHERE street IS NULL');
        $this->sql('ALTER TABLE delivery_addresses ALTER street SET NOT NULL');
        $this->sql('UPDATE delivery_addresses SET city = \'\' WHERE city IS NULL');
        $this->sql('ALTER TABLE delivery_addresses ALTER city SET NOT NULL');
        $this->sql('UPDATE delivery_addresses SET postcode = \'\' WHERE postcode IS NULL');
        $this->sql('ALTER TABLE delivery_addresses ALTER postcode SET NOT NULL');

        $this->sql('UPDATE delivery_addresses SET country_id = (
            SELECT cd.country_id
            FROM customers c
            JOIN country_domains cd ON c.domain_id = cd.domain_id
            WHERE c.id = delivery_addresses.customer_id
                AND cd.enabled = TRUE
            ORDER BY cd.country_id ASC
            LIMIT 1
        ) WHERE country_id IS NULL;');
        $this->sql('ALTER TABLE delivery_addresses ALTER country_id SET NOT NULL');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
