<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20161010151719 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE cart_items DROP CONSTRAINT FK_BEF48445A76ED395');
        $this->sql('
            ALTER TABLE
                cart_items
            ADD
                CONSTRAINT FK_BEF48445A76ED395 FOREIGN KEY (user_id)
                    REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
