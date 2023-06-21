<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200817110249 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE categories ADD over_limit_quantity INT DEFAULT NULL');
        $this->sql('ALTER TABLE payments ADD is_over_limit_payment BOOLEAN NOT NULL DEFAULT FALSE');
        $this->sql('ALTER TABLE payments ALTER is_over_limit_payment DROP DEFAULT');
        $this->sql('ALTER TABLE transports ADD is_over_limit_transport BOOLEAN NOT NULL DEFAULT FALSE');
        $this->sql('ALTER TABLE transports ALTER is_over_limit_transport DROP DEFAULT');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
