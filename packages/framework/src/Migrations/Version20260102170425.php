<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260102170425 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE notification_bars ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE notification_bars SET uuid = gen_random_uuid() WHERE uuid IS NULL');
        $this->sql('ALTER TABLE notification_bars ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_9547314BD17F50A6 ON notification_bars (uuid)');
    }
}
