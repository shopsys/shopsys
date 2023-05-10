<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200114084117 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE product_domains ADD short_description_usp1 VARCHAR(255) DEFAULT NULL');
        $this->sql('ALTER TABLE product_domains ALTER short_description_usp1 DROP DEFAULT');

        $this->sql('ALTER TABLE product_domains ADD short_description_usp2 VARCHAR(255) DEFAULT NULL');
        $this->sql('ALTER TABLE product_domains ALTER short_description_usp2 DROP DEFAULT');

        $this->sql('ALTER TABLE product_domains ADD short_description_usp3 VARCHAR(255) DEFAULT NULL');
        $this->sql('ALTER TABLE product_domains ALTER short_description_usp3 DROP DEFAULT');

        $this->sql('ALTER TABLE product_domains ADD short_description_usp4 VARCHAR(255) DEFAULT NULL');
        $this->sql('ALTER TABLE product_domains ALTER short_description_usp4 DROP DEFAULT');

        $this->sql('ALTER TABLE product_domains ADD short_description_usp5 VARCHAR(255) DEFAULT NULL');
        $this->sql('ALTER TABLE product_domains ALTER short_description_usp5 DROP DEFAULT');

        $this->sql('ALTER TABLE product_translations ADD name_prefix VARCHAR(255) DEFAULT NULL');
        $this->sql('ALTER TABLE product_translations ALTER name_prefix DROP DEFAULT');

        $this->sql('ALTER TABLE product_translations ADD name_sufix VARCHAR(255) DEFAULT NULL');
        $this->sql('ALTER TABLE product_translations ALTER name_sufix DROP DEFAULT');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
