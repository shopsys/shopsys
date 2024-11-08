<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20241104151802 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE price_lists (
                id SERIAL NOT NULL,
                domain_id INT NOT NULL,
                name VARCHAR(100) NOT NULL,
                last_update TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                valid_from TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                valid_to TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )');

        $this->sql('COMMENT ON COLUMN price_lists.last_update IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN price_lists.valid_from IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('COMMENT ON COLUMN price_lists.valid_to IS \'(DC2Type:datetime_immutable)\'');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
