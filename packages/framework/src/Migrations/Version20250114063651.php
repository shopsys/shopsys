<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20250114063651 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20210608120422')) {
            $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES (:name, :domainId, :value, :type)', [
                'name' => 'cspHeader',
                'domainId' => 0,
                'value' => 'default-src https: \'unsafe-inline\' \'unsafe-eval\' data:',
                'type' => 'string',
            ]);
        }
    }
}
