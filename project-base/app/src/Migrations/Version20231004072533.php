<?php

declare(strict_types=1);

namespace App\Migrations;

use App\Model\Transport\Type\TransportTypeEnum;
use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Migrations\MultidomainMigrationTrait;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20231004072533 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('INSERT INTO transport_types (code) VALUES (\'' . TransportTypeEnum::TYPE_PERSONAL_PICKUP . '\')');
        $lastTransportTypeId = $this->connection->lastInsertId('transport_types_id_seq');

        foreach ($this->getAllLocales() as $locale) {
            $this->sql(
                'INSERT INTO transport_type_translations (translatable_id, name, locale) 
                        VALUES (
                                ' . $lastTransportTypeId . ', 
                                \'' . t('Personal pickup', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale) . '\', 
                                \'' . $locale . '\'
                        )',
            );
        }

        $this->sql('UPDATE transports SET transport_type_id = ' . $lastTransportTypeId . ' WHERE personal_pickup = TRUE');

        $this->sql('ALTER TABLE transport_types ALTER code TYPE VARCHAR(25)');
        $this->sql('ALTER TABLE transports DROP personal_pickup');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
