<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20200504142810 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE images ADD akeneo_code VARCHAR(100) DEFAULT NULL');
        $this->sql('ALTER TABLE images ADD akeneo_image_type VARCHAR(100) DEFAULT NULL');
    }
}
