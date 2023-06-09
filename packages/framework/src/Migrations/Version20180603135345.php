<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20180603135345 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $categoriesCount = $this->sql('SELECT count(*) FROM categories')->fetchOne();
        if ($categoriesCount > 0) {
            return;
        }

        $this->sql('INSERT INTO categories (id, parent_id, level, lft, rgt) VALUES (1, null, 0, 1, 2)');
        $this->sql('ALTER SEQUENCE categories_id_seq RESTART WITH 2');
        $this->sql(
            'INSERT INTO category_domains (category_id, domain_id, enabled, visible) VALUES (1, 1, true, true)',
        );
        $this->sql('ALTER SEQUENCE category_domains_id_seq RESTART WITH 2');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
