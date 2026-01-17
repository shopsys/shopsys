<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240902171506 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE transport_prices ADD max_weight INT DEFAULT NULL');
        $this->sql('UPDATE transport_prices SET max_weight = (SELECT max_weight FROM transports WHERE transports.id = transport_prices.transport_id)');

        $this->sql('ALTER TABLE transport_prices DROP CONSTRAINT transport_prices_pkey');
        $this->sql('ALTER TABLE transport_prices ADD id SERIAL NOT NULL');
        $this->sql('ALTER TABLE transport_prices ADD PRIMARY KEY (id)');
    }
}
