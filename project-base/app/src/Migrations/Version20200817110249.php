<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20200817110249 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE categories ADD over_limit_quantity INT DEFAULT NULL');
        $this->sql('ALTER TABLE payments ADD is_over_limit_payment BOOLEAN NOT NULL DEFAULT FALSE');
        $this->sql('ALTER TABLE payments ALTER is_over_limit_payment DROP DEFAULT');
        $this->sql('ALTER TABLE transports ADD is_over_limit_transport BOOLEAN NOT NULL DEFAULT FALSE');
        $this->sql('ALTER TABLE transports ALTER is_over_limit_transport DROP DEFAULT');
    }
}
