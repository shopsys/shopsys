<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Ramsey\Uuid\Uuid;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200416114815 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE customer_user_refresh_token_chain ADD device_id UUID DEFAULT NULL');
        $this->sql('UPDATE customer_user_refresh_token_chain SET device_id = :uuid', ['uuid' => Uuid::uuid4()->toString()]);
        $this->sql('ALTER TABLE customer_user_refresh_token_chain ALTER device_id SET NOT NULL');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
