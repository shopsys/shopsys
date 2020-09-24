<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20200924145629 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->sql('insert into setting_values (name, domain_id, value, type) values 
            (\'scontoBridgeTransferOrderStatusLastUpdatedDatetime\', 0, \'1970-01-01T00:00:00+0000\', \'datetime\')
        ');
    }

    public function down(Schema $schema) : void
    {
    }
}
