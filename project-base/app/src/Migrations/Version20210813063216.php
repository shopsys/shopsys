<?php

declare(strict_types=1);

namespace App\Migrations;

use App\Model\Transport\Type\TransportTypeEnum;
use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20210813063216 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('INSERT INTO transport_types (code) VALUES (\'' . TransportTypeEnum::TYPE_PACKETERY . '\')');
        $lastTransportTypeId = $this->connection->lastInsertId('transport_types_id_seq');
        $this->sql('INSERT INTO transport_type_translations (translatable_id, name, locale) VALUES (' . $lastTransportTypeId . ', \'Zásilkovna\', \'cs\')');
        $this->sql('INSERT INTO transport_type_translations (translatable_id, name, locale) VALUES (' . $lastTransportTypeId . ', \'Packetery\', \'en\')');

        $this->sql('INSERT INTO tranfers (identifier, name) VALUES (\'PacketeryPacketsExport\', \'Packetery Send packet data to packetery\');');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
