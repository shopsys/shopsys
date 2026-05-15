<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20180409100239 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE personal_data_access_request ADD type VARCHAR(50)');
        $this->sql('UPDATE personal_data_access_request SET type = :type', ['type' => 'display']);
        $this->sql('ALTER TABLE personal_data_access_request ALTER COLUMN type SET NOT NULL');
    }
}
