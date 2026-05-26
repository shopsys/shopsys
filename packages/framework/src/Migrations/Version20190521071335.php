<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20190521071335 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE products ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE products SET uuid = uuid_generate_v4()');
        $this->sql('ALTER TABLE products ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_B3BA5A5AD17F50A6 ON products (uuid)');
    }
}
