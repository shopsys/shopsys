<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250331122654 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql("
            UPDATE mail_templates SET 
                subject = REPLACE(subject, '{total_price}', '{total_price_with_vat}'), 
                body = REPLACE(body, '{total_price}', '{total_price_with_vat}')
            WHERE
                order_status_id IS NOT NULL
        ");
    }
}
