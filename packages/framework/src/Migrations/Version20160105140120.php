<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20160105140120 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $sql = 'ALTER TABLE order_status_translations
            ALTER name SET NOT NULL;';
        $this->sql($sql);
    }
}
