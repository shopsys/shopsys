<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Migrations\MultidomainMigrationTrait;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20240702065648 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE categories DROP akeneo_code');
        $this->sql('ALTER TABLE flags DROP akeneo_code');
        $this->sql('ALTER TABLE images DROP akeneo_code');
        $this->sql('ALTER TABLE images DROP akeneo_image_type');
        $this->sql('ALTER TABLE parameter_groups DROP akeneo_code');
        $this->sql('ALTER TABLE parameters DROP akeneo_code');
        $this->sql('ALTER TABLE parameters DROP akeneo_type');
        $this->sql('ALTER TABLE units DROP akeneo_code');
        $this->sql('DELETE FROM setting_values WHERE name=\'akeneoTransferProductsLastUpdatedDatetime\'');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
