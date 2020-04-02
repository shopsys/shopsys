<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200402083757 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE ready_category_seo_mix_parameter_parameter_values DROP CONSTRAINT FK_428D0DF01452663E');
        $this->sql('ALTER TABLE ready_category_seo_mix_parameter_parameter_values DROP CONSTRAINT FK_428D0DF07C56DBD6');
        $this->sql('
            ALTER TABLE
                ready_category_seo_mix_parameter_parameter_values
            ADD
                CONSTRAINT FK_428D0DF01452663E FOREIGN KEY (parameter_value_id) REFERENCES parameter_values (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                ready_category_seo_mix_parameter_parameter_values
            ADD
                CONSTRAINT FK_428D0DF07C56DBD6 FOREIGN KEY (parameter_id) REFERENCES parameters (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
