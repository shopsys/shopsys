<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20200225102504 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE customers ADD domain_id INT NOT NULL DEFAULT 1');
        $this->sql(
            'UPDATE customers SET domain_id = (SELECT cu.domain_id FROM customer_users AS cu WHERE cu.customer_id = customers.id)',
        );
        $this->sql('ALTER TABLE customers ALTER domain_id DROP DEFAULT');
    }
}
