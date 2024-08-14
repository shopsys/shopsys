<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240814132958 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql(
            'UPDATE images
            SET position = (
                SELECT COUNT(*)
                FROM images sub_i
                WHERE sub_i.entity_name = images.entity_name
                  AND sub_i.entity_id = images.entity_id
                  AND (
                      (sub_i.type IS NULL AND images.type IS NULL) OR 
                      (sub_i.type = images.type)
                  )
            )
            WHERE position IS NULL',
        );
        $this->sql('ALTER TABLE images ALTER "position" SET NOT NULL');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
