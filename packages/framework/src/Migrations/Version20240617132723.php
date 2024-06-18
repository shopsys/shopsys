<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240617132723 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE orders ADD modified_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT now()');
        $this->sql('UPDATE orders SET modified_at = created_at');
        $this->sql('ALTER TABLE orders ALTER modified_at DROP DEFAULT');

        $this->sql('ALTER TABLE order_items ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE order_items SET uuid = uuid_generate_v4()');
        $this->sql('ALTER TABLE order_items ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_62809DB0D17F50A6 ON order_items (uuid)');

        $this->sql('ALTER TABLE order_items ADD added_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT now()');
        $this->sql('ALTER TABLE order_items ALTER added_at DROP DEFAULT');

        $this->sql('ALTER TABLE orders ALTER number DROP NOT NULL');
        $this->sql('ALTER TABLE orders ALTER url_hash DROP NOT NULL');
        $this->sql('ALTER TABLE orders ALTER transport_id DROP NOT NULL');
        $this->sql('ALTER TABLE orders ALTER payment_id DROP NOT NULL');
        $this->sql('ALTER TABLE orders ALTER country_id DROP NOT NULL');
        $this->sql('ALTER TABLE orders ALTER created_at DROP NOT NULL');
        $this->sql('ALTER TABLE orders ALTER email DROP NOT NULL');
        $this->sql('ALTER TABLE orders ALTER telephone DROP NOT NULL');
        $this->sql('ALTER TABLE orders ALTER street DROP NOT NULL');
        $this->sql('ALTER TABLE orders ALTER city DROP NOT NULL');
        $this->sql('ALTER TABLE orders ALTER postcode DROP NOT NULL');
        $this->sql('ALTER TABLE orders ALTER delivery_street DROP NOT NULL');
        $this->sql('ALTER TABLE orders ALTER delivery_city DROP NOT NULL');
        $this->sql('ALTER TABLE orders ALTER delivery_postcode DROP NOT NULL');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
