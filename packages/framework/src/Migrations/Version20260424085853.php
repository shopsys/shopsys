<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20260424085853 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE ready_category_seo_mix_parameter_parameter_values DROP CONSTRAINT fk_428d0df01452663e');
        $this->sql('ALTER TABLE ready_category_seo_mix_parameter_parameter_values ADD CONSTRAINT fk_428d0df01452663e FOREIGN KEY (parameter_value_id) REFERENCES parameter_values (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
