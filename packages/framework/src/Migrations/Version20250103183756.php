<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250103183756 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE complaints ADD email VARCHAR(255) DEFAULT NULL');
        $this->sql(
            'UPDATE complaints 
            SET email = CASE
                WHEN customer_user_id IS NOT NULL THEN
                    (SELECT email FROM customer_users WHERE id = complaints.customer_user_id)
                ELSE
                    (SELECT email FROM orders WHERE id = complaints.order_id)
            END;',
        );
        $this->sql('ALTER TABLE complaints ALTER email DROP DEFAULT');
        $this->sql('ALTER TABLE complaints ALTER email SET NOT NULL');
    }
}
