<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200119160953 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES
            (\'akeneoTransferProductsLastUpdatedDatetime\', 0, \'1970-01-01T00:00:00+0000\', \'datetime\')
        ');
    }
}
