<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240613105832 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200507094948')) {
            $this->sql('ALTER TABLE parameters ADD parameter_type VARCHAR(100) NOT NULL DEFAULT \'checkbox\'');
            $this->sql('ALTER TABLE parameters ALTER parameter_type DROP DEFAULT');
            $this->sql('ALTER TABLE parameter_values ADD rgb_hex VARCHAR(10) DEFAULT NULL');
        }

        if ($this->isAppMigrationNotInstalled('Version20210520112854')) {
            $this->sql('ALTER TABLE parameters ADD unit_id INT DEFAULT NULL');
            $this->sql('
                ALTER TABLE
                    parameters
                ADD
                    CONSTRAINT FK_69348FEF8BD700D FOREIGN KEY (unit_id) REFERENCES units (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('CREATE INDEX IDX_69348FEF8BD700D ON parameters (unit_id)');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200910092545')) {
            $this->sql('ALTER TABLE parameter_values ALTER text TYPE TEXT');
        }

        if ($this->isAppMigrationNotInstalled('Version20200220124729')) {
            $this->sql('ALTER TABLE parameters ADD ordering_priority INT DEFAULT 0 NOT NULL');
            $this->sql('ALTER TABLE parameters ALTER ordering_priority DROP DEFAULT');
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
