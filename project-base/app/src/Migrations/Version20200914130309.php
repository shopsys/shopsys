<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200914130309 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE transports ADD delivery_code VARCHAR(10) NOT NULL DEFAULT \'fixme\'');
        $this->sql('ALTER TABLE transports ALTER delivery_code DROP DEFAULT');
        $this->sql('ALTER TABLE transports ADD type_of_delivery_key INT NOT NULL DEFAULT 1');
        $this->sql('ALTER TABLE transports ALTER type_of_delivery_key DROP DEFAULT');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
