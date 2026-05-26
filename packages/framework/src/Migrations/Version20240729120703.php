<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20240729120703 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE customer_user_role_groups ADD uuid UUID NOT NULL DEFAULT uuid_generate_v4()');
        $this->sql('ALTER TABLE customer_user_role_groups ALTER uuid DROP DEFAULT');
        $this->sql('CREATE UNIQUE INDEX UNIQ_E2F14348D17F50A6 ON customer_user_role_groups (uuid)');
    }
}
