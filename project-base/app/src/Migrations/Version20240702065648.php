<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Migrations\MultidomainMigrationTrait;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20240702065648 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE categories DROP COLUMN IF EXISTS akeneo_code');
        $this->sql('ALTER TABLE flags DROP COLUMN IF EXISTS akeneo_code');
        $this->sql('ALTER TABLE images DROP COLUMN IF EXISTS akeneo_code');
        $this->sql('ALTER TABLE images DROP COLUMN IF EXISTS akeneo_image_type');
        $this->sql('ALTER TABLE units DROP COLUMN IF EXISTS akeneo_code');
        $this->sql('DELETE FROM setting_values WHERE name=\'akeneoTransferProductsLastUpdatedDatetime\'');
    }
}
