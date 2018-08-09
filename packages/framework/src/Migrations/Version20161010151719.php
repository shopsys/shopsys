<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20161010151719 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('ALTER TABLE cart_items DROP CONSTRAINT FK_BEF48445A76ED395');
        $this->sql('
            ALTER TABLE
                cart_items
            ADD
                CONSTRAINT FK_BEF48445A76ED395 FOREIGN KEY (user_id)
                    REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema)
    {
    }
}
