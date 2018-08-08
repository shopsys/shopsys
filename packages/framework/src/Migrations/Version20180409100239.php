<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20180409100239 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE personal_data_access_request ADD type VARCHAR(50)');
        $this->sql('UPDATE personal_data_access_request SET type = :type', ['type' => PersonalDataAccessRequest::TYPE_DISPLAY]);
        $this->sql('ALTER TABLE personal_data_access_request ALTER COLUMN type SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
    }
}
