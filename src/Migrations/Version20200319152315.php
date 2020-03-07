<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200319152315 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE ready_category_seo_mix_parameter_parameter_values DROP CONSTRAINT FK_428D0DF07C7FCEDE');
        $this->sql('
            ALTER TABLE
                ready_category_seo_mix_parameter_parameter_values
            ADD
                CONSTRAINT FK_428D0DF07C7FCEDE FOREIGN KEY (ready_category_seo_mix_id) REFERENCES ready_category_seo_mixes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
