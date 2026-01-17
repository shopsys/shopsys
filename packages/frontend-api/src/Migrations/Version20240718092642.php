<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240718092642 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE customer_user_login_types ADD last_logged_in_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT now()');
        $this->sql('ALTER TABLE customer_user_login_types ALTER last_logged_in_at DROP DEFAULT');
    }
}
