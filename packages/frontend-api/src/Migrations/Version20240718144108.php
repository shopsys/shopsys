<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240718144108 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE customer_user_login_types ADD external_id TEXT DEFAULT NULL');
    }
}
