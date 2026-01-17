<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240816164022 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE complaints ADD domain_id INT DEFAULT NULL');

        $this->sql('UPDATE complaints SET domain_id = orders.domain_id FROM orders WHERE complaints.order_id = orders.id');

        $this->sql('ALTER TABLE complaints ALTER domain_id SET NOT NULL');
    }
}
