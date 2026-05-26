<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20251006120000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200825083150')) {
            $this->sql('ALTER TABLE product_domains ADD calculated_selling_denied BOOLEAN NOT NULL DEFAULT FALSE');
            $this->sql('ALTER TABLE product_domains ALTER calculated_selling_denied DROP DEFAULT');
        } else {
            $this->sql('ALTER TABLE product_domains RENAME COLUMN calculated_sale_exclusion TO calculated_selling_denied');
        }

        $this->sql('ALTER TABLE product_domains RENAME COLUMN sale_exclusion TO selling_denied');
        $this->sql('ALTER TABLE products DROP COLUMN calculated_selling_denied');
    }
}
