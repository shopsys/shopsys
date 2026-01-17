<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250209123001 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('CREATE INDEX complaint_email_trgm_idx ON complaints USING gin (normalized(email) gin_trgm_ops);');
        $this->sql('CREATE INDEX complaint_manual_document_number_trgm_idx ON complaints USING gin (normalized(manual_document_number) gin_trgm_ops);');
        $this->sql('CREATE INDEX complaint_delivery_last_name_trgm_idx ON complaints USING gin (normalized(delivery_last_name) gin_trgm_ops);');
        $this->sql('CREATE INDEX complaint_delivery_company_name_trgm_idx ON complaints USING gin (normalized(delivery_company_name) gin_trgm_ops);');
    }
}
