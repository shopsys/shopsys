<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20200416114815 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE customer_user_refresh_token_chain ADD device_id UUID DEFAULT NULL');
        $this->sql('UPDATE customer_user_refresh_token_chain SET device_id = uuid_generate_v4()');
        $this->sql('ALTER TABLE customer_user_refresh_token_chain ALTER device_id SET NOT NULL');
    }
}
