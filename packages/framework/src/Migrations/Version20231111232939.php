<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20231111232939 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE products DROP COLUMN recalculate_availability');

        if ($this->columnExists('products', 'calculated_availability_id')) {
            $this->sql('ALTER TABLE products DROP calculated_availability_id');
        }
    }
}
