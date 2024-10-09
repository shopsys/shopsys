<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20241008072724 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('DROP SEQUENCE IF EXISTS complaints_id_seq');
        $this->sql('DROP SEQUENCE IF EXISTS complaint_items_id_seq');

        $this->sql('CREATE SEQUENCE complaints_id_seq');
        $this->sql('SELECT setval(\'complaints_id_seq\', (SELECT MAX(id) FROM complaints))');
        $this->sql('ALTER TABLE complaints ALTER id SET DEFAULT nextval(\'complaints_id_seq\')');
        $this->sql('CREATE SEQUENCE complaint_items_id_seq');
        $this->sql('SELECT setval(\'complaint_items_id_seq\', (SELECT MAX(id) FROM complaint_items))');
        $this->sql('ALTER TABLE complaint_items ALTER id SET DEFAULT nextval(\'complaint_items_id_seq\')');

        $this->sql('ALTER SEQUENCE complaint_items_id_seq OWNED BY complaint_items.id');
        $this->sql('ALTER SEQUENCE complaints_id_seq OWNED BY complaints.id');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
