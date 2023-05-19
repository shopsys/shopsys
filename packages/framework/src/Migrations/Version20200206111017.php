<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200206111017 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE OR REPLACE FUNCTION set_export_product_by_product_visibility() RETURNS trigger AS $$
                BEGIN
                    IF TG_OP = \'INSERT\' OR NEW.visible <> OLD.visible THEN
                        UPDATE products p
                        SET export_product = TRUE
                        WHERE p.id = NEW.product_id;
                    END IF;
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        ');

        $this->sql('DROP TRIGGER IF EXISTS mark_product_for_export ON product_visibilities');
        $this->sql('
            CREATE TRIGGER mark_product_for_export
            AFTER INSERT OR UPDATE OF visible
            ON product_visibilities
            FOR EACH ROW
            EXECUTE PROCEDURE set_export_product_by_product_visibility();
        ');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
