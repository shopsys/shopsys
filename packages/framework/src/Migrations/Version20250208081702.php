<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20250208081702 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE complaints ADD manual_document_number VARCHAR(255) DEFAULT NULL');
        $this->sql('ALTER TABLE complaints ALTER order_id DROP NOT NULL');
        $this->sql('ALTER TABLE complaint_items ALTER catnum DROP NOT NULL');
    }
}
