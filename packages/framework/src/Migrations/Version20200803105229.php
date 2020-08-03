<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200803105229 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE newsletter_subscribers ALTER created_at DROP DEFAULT');
        $this->sql('ALTER TABLE cron_modules ALTER suspended DROP DEFAULT');
        $this->sql('ALTER TABLE cron_modules ALTER enabled DROP DEFAULT');
        $this->sql('ALTER TABLE products ALTER recalculate_availability DROP DEFAULT');
        $this->sql('ALTER TABLE products ALTER recalculate_price DROP DEFAULT');
        $this->sql('ALTER TABLE products ALTER recalculate_visibility DROP DEFAULT');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
