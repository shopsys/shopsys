<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200430075430 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE newsletter_subscribers ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT NOW()');
        $this->sql('COMMENT ON COLUMN newsletter_subscribers.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->sql('ALTER TABLE newsletter_subscribers ALTER updated_at DROP DEFAULT;');

        $this->sql('ALTER TABLE newsletter_subscribers ADD deleted BOOLEAN NOT NULL DEFAULT false');
        $this->sql('ALTER TABLE newsletter_subscribers ALTER deleted DROP DEFAULT;');

        $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES
            (\'targitoLastSyncDatetime\', 0, \'1970-01-01T00:00:00+0000\', \'datetime\')
        ');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
