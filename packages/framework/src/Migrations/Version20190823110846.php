<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20190823110846 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE products ALTER selling_from TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->sql('ALTER TABLE products ALTER selling_from DROP DEFAULT');
        $this->sql('ALTER TABLE products ALTER selling_to TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->sql('ALTER TABLE products ALTER selling_to DROP DEFAULT');
    }
}
