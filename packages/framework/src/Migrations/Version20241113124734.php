<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20241113124734 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200114084117')) {
            $this->sql('ALTER TABLE product_translations ADD name_prefix VARCHAR(255) DEFAULT NULL');
            $this->sql('ALTER TABLE product_translations ALTER name_prefix DROP DEFAULT');

            $this->sql('ALTER TABLE product_translations ADD name_sufix VARCHAR(255) DEFAULT NULL');
            $this->sql('ALTER TABLE product_translations ALTER name_sufix DROP DEFAULT');
        }
    }
}
