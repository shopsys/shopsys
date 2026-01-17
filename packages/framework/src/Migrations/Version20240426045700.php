<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240426045700 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('DELETE FROM setting_values WHERE name = :name', ['name' => 'shopInfoPhoneNumber']);
        $this->sql('DELETE FROM setting_values WHERE name = :name', ['name' => 'shopInfoEmail']);
        $this->sql('DELETE FROM setting_values WHERE name = :name', ['name' => 'shopInfoPhoneHours']);
    }
}
