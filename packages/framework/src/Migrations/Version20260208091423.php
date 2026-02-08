<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20260208091423 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('UPDATE setting_values SET value = :value WHERE name = :name AND domain_id = :domainId AND value = :oldValue', [
            'value' => "frame-ancestors 'self'; default-src 'self' https: 'unsafe-inline' data:",
            'name' => 'cspHeader',
            'domainId' => 0,
            'oldValue' => "default-src https: 'unsafe-inline' 'unsafe-eval' data:",
        ]);
    }
}
